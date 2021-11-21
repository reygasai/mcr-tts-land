'use strict';

$(document).ready(() => {
  let form = $('.register-content').find('form');

  $('.register-content button').click(function(){
    let username = form.find('#input-login').val(),
    email = form.find('#input-email').val(),
    password = form.find('#input-password').val(),
    repassword = form.find('#input-password-confirm').val(),
    checked = form.find('#check-terms').prop('checked');

    if (checked === false) return app.toast('Ошибка!', 'Вы должны согласится с правилами!');
    if (password !== repassword) return app.toast('Ошибка', 'Повторите пароль!');
    $.ajax({
      url: '/index.php?mode=ajax&do=register',
      type: 'POST',
      dataType: 'JSON',
      data: {
        csrf_secure_key: csrf_key,
        login: username,
        email: email,
        password: password,
        repassword: repassword,
        rules: checked ? 1 : 0
      }
    }).done((response) => {
      if (response._type === true) setTimeout(() => { location.href = '/'; }, 5000);
      app.toast(response._title, response._message, (response._type) ? 2 : 1)
    });
  });
});
