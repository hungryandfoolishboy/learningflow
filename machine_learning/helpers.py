from numba import jit
from pandas as pd

@jit
def check_missing(dataframe, head=10):
    total = dataframe.isnull().sum().sort_values(ascending=False)
    percent = (dataframe.isnull().sum()/ len(dataframe) * 100).sort_values(ascending=False)
    missing_application_train_data  = pd.concat([total, percent], axis=1, keys=['Total', 'Percent'])
    print(missing_application_train_data.head(head))