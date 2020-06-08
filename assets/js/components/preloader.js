export default function constructPreloader($container) {
  const hiddenClass = 'hh-pending-info--hidden';

  return {
    show: () => {
      $container.removeClass(hiddenClass);
    },
    hide: () => {
      $container.addClass(hiddenClass);
    },
  };
}
