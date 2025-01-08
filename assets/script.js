jQuery(document).ready(function ($) {
  const postID = popupData.post_id;
  console.log(popupData);
  // Cookie functionality
  function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
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

  // Obtener el slug de la página actual
  const currentSlug = window.location.pathname.split('/').filter(Boolean).pop(); // Obtiene el último segmento de la URL
  alert(currentSlug);
  // Función para mostrar el popup
  function showPopup(popup) {
    const cookieName = `popup_shown_post_${postID}`;

    // Si la cookie no existe, es decir, el popup no se ha cerrado anteriormente
    if (!getCookie(cookieName)) {
      const popupOverlay = $(`<div class="acf-popup-overlay" id="popup-overlay-${popup.id}"></div>`);
      const popupContent = $(`
        <div class="acf-popup-content popup-${popup.style}">
            <button class="acf-popup-close">X</button>
            <h2 class="acf-popup-title">${popup.title}</h2>
            <div class="acf-popup-body">${popup.content}</div>
            ${popup.buttonLabel && popup.buttonUrl
          ? `<a href="${popup.buttonUrl}" class="acc-popup-button" target="${popup.buttonTarget}">${popup.buttonLabel}</a>`
          : ''
        }
        </div>
      `);

      popupOverlay.append(popupContent);
      $('body').append(popupOverlay);

      // Mostrar el pop-up
      popupOverlay.fadeIn();

      // Cerrar el pop-up
      popupOverlay.on('click', function (e) {
        if ($(e.target).is('.acf-popup-close') || $(e.target).is(`#popup-overlay-${popup.id}`)) {
          popupOverlay.fadeOut();
          setCookie(cookieName, 'true', popup.cookieExpiration); // Establecer cookie para no mostrarlo nuevamente
        }
      });
    }
  }

  // Función para verificar si el usuario ha desplazado un 50% de la página
  function checkScrollPosition() {
    const scrollTop = $(window).scrollTop(); // Posición actual del scroll
    const docHeight = $(document).height(); // Altura total del documento
    const winHeight = $(window).height(); // Altura de la ventana
    const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;

    // Si el usuario ha desplazado más del 50% de la página
    if (scrollPercent > 33) {
      // Iterar sobre todos los popups en popupData.popup_data
      if (Array.isArray(popupData.popup_data)) {
        popupData.popup_data.forEach(function (popup) {
          console.log(popup.pages);
          // Verificar si el pop-up debe mostrarse
          if ((popup.allPages || popup.pages.includes(currentSlug)) && !getCookie(`popup_closed_${popup.id}_${postID}`)) {
            // Llamar a la función para mostrar el popup solo si no se ha cerrado previamente
            showPopup(popup);
            // Establecer la cookie para evitar que se muestre nuevamente
            setCookie(`popup_closed_${popup.id}_${postID}`, 'true', popup.cookieExpiration || 365); // Cookie expira en 365 días
          }
        });
      }
    }
  }

  // Ejecutar la función de verificación del scroll cuando el usuario se desplace
  $(window).on('scroll', function () {
    checkScrollPosition();
  });

  // También verificar al cargar la página por si el usuario ya ha desplazado
  checkScrollPosition();
});
