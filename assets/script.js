jQuery(document).ready(function ($) {
  // Cookie functionality
  function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
  }

  function getCookie(name) {
    const decodedCookies = decodeURIComponent(document.cookie);
    const cookies = decodedCookies.split(';');
    for (let i = 0; i < cookies.length; i++) {
      const cookie = cookies[i].trim();
      if (cookie.indexOf(name + "=") === 0) {
        return cookie.substring(name.length + 1);
      }
    }
    return null;
  }

  // Show the pop up if the cookie doesn't exists
  if (!getCookie('popup_closed')) {
    const popupOverlay = $('<div class="acf-popup-overlay"></div>');
    const popupContent = $(`
          <div class="acf-popup-content">
              <button class="acf-popup-close">X</button>
              <h2 class="acf-popup-title">${popupData.title}</h2>
              <div class="acf-popup-body">${popupData.content}</div>
              ${popupData.buttonLabel && popupData.buttonUrl
        ? `<a href="${popupData.buttonUrl}" class="acc-popup-button" target="${popupData.buttonTarget}">${popupData.buttonLabel}</a>`
        : ''
      }
          </div>
      `);

    popupOverlay.append(popupContent);
    $('body').append(popupOverlay);

    // Show Pop Up
    popupOverlay.fadeIn();

    // Close Pop Up
    $('.acf-popup-close, .acf-popup-overlay').on('click', function (e) {
      if ($(e.target).is('.acf-popup-close') || $(e.target).is('.acf-popup-overlay')) {
        popupOverlay.fadeOut();
        setCookie('popup_closed', 'true', popupData.cookieExpiration);
      }
    });
  }
});
