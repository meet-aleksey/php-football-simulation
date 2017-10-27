﻿# Football simulation

This is a simple simulator of football matches on PHP.

## Input data

At the input, a CSV file with the following structure is expected:

```
Country;Total games;Wins;Draws;Losses;Scored and missed goals
```

Columns delimiter - `;`.

Total columns - `6`.

The 2-5 columns must contain integer values.

The last column expects two numeric values separated by a hyphen.

First line - is header.

After the first line a list of football teams is expected.
The number of teams must be divisible by two.

![Preview](preview.gif)

## Requirements

* PHP 5.x/7.x

## License

The MIT License (MIT)

Copyright © 2017, [@meet-aleksey](https://github.com/meet-aleksey)