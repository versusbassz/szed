# Szed
Szed = Attachments **S**i**Z**es **ED**itor

- editor for usual attacments sizes
- uses "Fly Dynamic Images Resizer" as a fallback (experimental feature)

## Restrictions
- works only with `{uploads-path}/{year}}/{month}}/{file-name}.{ext}` structure
- doesnt work with non-boolean crop params, e.g.: `add_image_size(... crop=\[left, center\])`
- doesnt have i18n, fow now

## Requirements
- PHP 7.1+
- Wordpress 5.3+
- display resolution (minimal): 1366x768
- browsers: old browsers are not supported, only latest versions

## How to start development
- `make build`
- start `VVV` instance with proper settings (will be described later...)
