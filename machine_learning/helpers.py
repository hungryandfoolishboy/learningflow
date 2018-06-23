from numba import jit
from pandas as pd
import time
from contextlib import contextmanager

@jit
def check_missing_values(dataframe, head=10):
    total = dataframe.isnull().sum().sort_values(ascending=False)
    percent = (dataframe.isnull().sum()/ len(dataframe) * 100).sort_values(ascending=False)
    missing_application_train_data  = pd.concat([total, percent], axis=1, keys=['Total', 'Percent'])
    print(missing_application_train_data.head(head))

@contextmanager
def timer(title):
    t0 = time.time()
    yield
    print("{} - done in {:.0f}s".format(title, time.time() - t0))

@jit
def one_hot_encoder(df, nan_as_category = True):
    original_columns = list(df.columns)
    categorical_columns = [col for col in df.columns if df[col].dtype == 'object']
    df = pd.get_dummies(df, columns= categorical_columns, dummy_na= nan_as_category)
    new_columns = [c for c in df.columns if c not in original_columns]
    return df, new_columns