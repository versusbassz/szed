module.exports = {
  'plugins': [
    "stylelint-order"
  ],
  'extends': 'stylelint-config-sass-guidelines',
  'rules': {
    'block-no-empty': null,
    'indentation': 4,
    'max-nesting-depth': 2,
    'selector-class-pattern': '^[a-z]+[a-z0-9\-\_]+$',

    "order/properties-alphabetical-order": null,
    'order/properties-order': [
      [
        {
          'groupName': 'props-block',
          'emptyLineBefore': 'never',
          'properties': [
            'float',
            'display',
            'flex-direction',
            'align-items',
            'justify-content',
            'flex-wrap',
            'width',
            'max-width',
            'min-width',
            'height',
            'max-height',
            'min-height',
            'margin',
            'margin-top',
            'margin-bottom',
            'margin-left',
            'margin-right',
            'border',
            'padding',
            'padding-top',
            'padding-bottom',
            'padding-left',
            'padding-right',
            'border-box',
          ],
        },
        {
          'groupName': 'props-positional',
          'emptyLineBefore': 'threshold',
          'properties': [
            'position',
            'top',
            'bottom',
            'left',
            'right',
            'z-index',
            'overflow-x',
            'overflow-y',
          ],
        },
        {
          'groupName': 'props-text',
          'emptyLineBefore': 'threshold',
          'properties': [
            'color',
            'font',
            'font-style',
            'font-weight',
            'font-size',
            'line-height',
            'font-family',
            'text-align',
            'text-decoration',
            'text-transform',
          ],
        },
        {
          'groupName': 'props-visual',
          'emptyLineBefore': 'threshold',
          'properties': [
            'opacity',
            'background-color',
            'background',
            'border-radius',
            'box-shadow',
            'transition',
          ],
        },
        {
          'groupName': 'props-misc',
          'emptyLineBefore': 'threshold',
          'properties': [
            'cursor',
          ],
        },
      ],
      {
        'unspecified': 'bottomAlphabetical',
        'emptyLineMinimumPropertyThreshold': 3
      }
    ]
  }
}
