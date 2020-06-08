/* eslint-disable import/prefer-default-export */

export function log(data, name = '') {
  /* eslint-disable no-console */
  if (szed.debug === true) {
    if (name) {
      console.log(name);
    }

    console.log(data);
  }
  /* eslint-enable no-console */
}
