# learning propress
What's going on?

  * [Machine learning](#machine-learning-leaderboard)
  * [Feature engineering](#feature-engineering-leaderboard)
  * [Helpers](#helpers-py)
  * [Kafka](https://github.com/clotyxf/learningflow/tree/master/Kafka/README.md)
  * [PHP](https://github.com/clotyxf/learningflow/tree/master/php/README.md)

## Machine learning \[[leaderboard](https://github.com/clotyxf/learningflow/tree/master/machine_learning/README.md)\]:

* Ensemble Model _2018.06_ \[[ipynb](https://github.com/clotyxf/learningflow/tree/master/machine_learning/ensemble_scikit_learn.ipynb)\]

* Ensemble XGB _2018.06_ \[[ipynb](https://github.com/clotyxf/learningflow/tree/master/machine_learning/ensemble_end_to_end.ipynb)\]

## Feature engineering \[[leaderboard](https://github.com/clotyxf/learningflow/tree/master/machine_learning/README.md)\]:

* Feature selection _2018.06_ \[[ipynb](https://github.com/clotyxf/learningflow/tree/master/machine_learning/feature_selection.ipynb)\]

## Helpers \[[py](https://github.com/clotyxf/learningflow/tree/master/machine_learning/helpers.py)\]:

> mixed check_missing_values(dataframe data, int head)
```
  print(missing values)
```

> str timer(str title)
```
  with timer('xxx'):
    pass
```

> mixed fix_missing_value(dataframe df, str col, str boosting_type='mean')
```
  return new_dataframe
```

> mixed one_hot_encoder(dataframe df, boolean nan_as_category)
```
  return new_dataframe, new_columns
```

> mixed freq_encoding(dataframe df, list cols, boolean drop=False)

```
  return new_dataframe
```

> mixed binary_encoding(dataframe train_df, dataframe test_df, str col)
```
  return new_train_df, new_test_df
```

> mixed  _corr_matrix(dataframe df, float threshold=0.9)
```
  return drop_columns
```