# ivt/pcre

PCRE class to wrap `preg_*()` functions.

## Purpose

There are the following problems with the `preg_*()` functions in PHP:

1. You have to check `preg_last_error()` every time you use them.
2. Regular expresion strings are not easily composable because they must all agree on a delimiter to use which needs to be escaped in addition to the standard regular expression characters (`.\+*?[^]$(){}=!<>|:-`).
3. The returned array structure from `preg_match()` and `preg_match_all()` is not very "friendly" and depends on the combination of `PREG_OFFSET_CAPTURE` and `PREG_PATTERN_ORDER` or `PREG_PATTERN_ORDER` passed as `$flags`.
4. The returned array structure from `preg_match()` and `preg_match_all()` excludes sub groups which didn't match, _unless_ a subgroup after them matched, in which they exist with offset `-1` and string `""`.
5. The `$limit` passed to `preg_replace()` and `preg_split()` uses a sentinel of `-1` as "no limit", meaning you must remember to use `max(0, ...)` if you are computing the limit to use.

These are resolved as follows:

1. `preg_last_error()` is checked automatically and a `PCREException` is thrown in the case of an error.
2. The regular expression is passed _without_ delimiters, and options that would normally occur after the last delimiter are passed seperately.
3. `PCRE::match()` and `PCRE::matchAll()` return dedicated `PCREMatch` objects.
4. Matches from `preg_match_all()` with offset `-1` are filtered out.
5. The `$limit` is instead `int|null` with `null` meaning "no limit".
