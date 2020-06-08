export default function constructErrorsBlock($container) {
  return {
    show: (errors) => {
      let content = '';

      Object.entries(errors).forEach(([code, message]) => {
        content += `<p>${message} (${code})</p>`;
      });

      $container.html(content);
      $container.show();
    },
    hide: () => {
      $container.hide();
    },
  };
}
