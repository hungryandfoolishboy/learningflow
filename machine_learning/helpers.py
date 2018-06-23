from numba import jit
from pandas as pd
import time
import gc
from contextlib import contextmanager


@jit
def check_missing_values(dataframe, head=10):
    total = dataframe.isnull().sum().sort_values(ascending=False)
    percent = (dataframe.isnull().sum() / len(dataframe)
               * 100).sort_values(ascending=False)
    missing_application_train_data = pd.concat(
        [total, percent], axis=1, keys=['Total', 'Percent'])
    print(missing_application_train_data.head(head))


@contextmanager
def timer(title):
    t0 = time.time()
    yield
    print("{} - done in {:.0f}s".format(title, time.time() - t0))


def one_hot_encoder(df, nan_as_category=True):
    original_columns = list(df.columns)
    categorical_columns = [
        col for col in df.columns if df[col].dtype == 'object']
    df = pd.get_dummies(df, columns=categorical_columns,
                        dummy_na=nan_as_category)
    new_columns = [c for c in df.columns if c not in original_columns]
    return df, new_columns


def fix_missing_value(df, col, boosting_type='mean'):
    null_count = df[col].value_counts()[0]
    if boosting_type == 'mean':
        col_mean = df[col].replace(0, np.NaN).mean()
        fill_values = [col_mean] * null_count
    elif boosting_type == 'median':
        median = df[col].replace(0, np.NaN).median()
        fill_values = [median] * null_count
    else:
        col_mean = df[col].replace(0, np.NaN).mean()
        col_std = df[col].replace(0, np.NaN).std()
        col_val = col_mean - col_std
        fill_values = np.random.randint(
            min_val if min_val > 0 else 0, a_mean + a_std, size=null_count)
    df[col][df[col] == 0] = fill_values
    return df


def freq_encoding(df, cols, drop=True):
    for col in cols:
        col_freq = col + '_freq'
        freq_df = df[col].value_counts()
        freq_df = pd.DataFrame(freq_df)
        freq_df.reset_index(inplace=True)
        freq_df.columns = [[col, col_freq]]

        freq_df = pd.merge(df[[col]], freq_df, how='left', on=col)
        if drop:
            freq_df.drop([col], axis=1, inplace=True)
    df=pd.concat([df, freq_df], axis=1)
    del freq_df
    gc.collect()
    return df

def binary_encoding(train_df, test_df, col):
    union_val = np.union1d(train_df[col].unique(), test_df[col].unique())
    max_dec = union_val.max()

    max_bin_len = len("{0:b}".format(max_dec))
    index = np.arange(len(union_val))
    columns = list([col])

    bin_df = pd.DataFrame(index=index, columns=columns)
    bin_df[col] = union_val

    col_bin = bin_df[col].apply(lambda x: "{0:b}".format(x).zfill(max_bin_len))

    splitted = col_bin.apply(lambda x: pd.Series(list(x)).astype(np.uint8))
    splitted.columns = [col + '_bin_' + str(x) for x in splitted.columns]
    bin_df = bin_df.join(splitted)

    train_df = pd.merge(train_df, bin_df, how='left', on=[col])
    test_df = pd.merge(test_df, bin_df, how='left', on=[col])

    del bin_df, col_bin
    gc.collect()

    return train_df, test_df
