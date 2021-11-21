let profile = {
  update_password: (old_password, password, password_repeat) => {
    $.post('index.php?mode=ajax&do=modules|profile|actions', {
      action: 'update_password',
      old_password: old_password,
      password: password,
      password_repeat: password_repeat,
      csrf_secure_key: csrf_key
    }).done(response => {
      app.toast(response.message, '', response.type ? 2 : 1)
    });
  },
  buy_group: (group_id, server_id) => {
    $.post('index.php?mode=ajax&do=modules|profile|actions', {
      action: 'buy_group',
      group_id: group_id,
      server_id: server_id,
      csrf_secure_key: csrf_key
    }).done(response => {
      app.toast(response.message, '', response.type ? 2 : 1)
    });
  },
  giftcode: giftcode => {
    $.post('index.php?mode=ajax&do=modules|profile|actions', {
      action: 'giftcode',
      giftcode: giftcode,
      csrf_secure_key: csrf_key
    }).done(response => {
      app.toast(response.message, '', response.type ? 2 : 1)
    });
  },
  load_server: server => {
    let server_id = server.attr('data-server-id')
    items = $('.group-list'),
    itemsActive = items.find('.zoomIn');
    if (items.find('[data-server-id=' + server_id + ']').length <= 0) {
      app.toast('Ошибка', 'На сервере нет групп для покупки!', 1)
      return;
    }
    $('.server-list').slideUp(200);
    setTimeout(function(){
      $('.selected-server > .title').html($(server).find('.name').html());
      $('.selected-server').slideDown();
      $('.group-list').slideDown();
    }, 400);
    if (itemsActive != 0) {
      itemsActive.each(function(){
        $(this).removeClass('animated zoomIn zoomOut').addClass('animated zoomOut').fadeOut();
      });
    }
    setTimeout(function(){
      items.find('[data-server-id=' + server_id + ']').each(function(){
        $(this).removeClass('animated zoomIn zoomOut').addClass('animated zoomIn').fadeIn();
      });
    }, 800);
  }
};

$(document).ready(()=>{
  $('[data-form=update_password] [data-trigger=submit]').click(function(){
    let form = $(this).parents('[data-form=update_password]'),
        old_password = form.find('input[name=old_password]').val(),
        password = form.find('input[name=password]').val(),
        password_repeat = form.find('input[name=password_repeat]').val();
    profile.update_password(old_password, password, password_repeat);
  });
  $('[data-form=giftcode] [data-trigger=submit]').click(function(){
    let giftcode = $(this).parents('[data-form=giftcode]').find('input[name=giftcode]').val();
    profile.giftcode(giftcode);
  });
  $('.selected-server > button').click(()=>{
    $('.server-list').slideDown();
    $('.item-list').slideUp(400);
    $('.selected-server').slideUp(600);
    $('.group-list > [data-server-id]').each(function(){
      $(this).removeClass('animated zoomIn zoomOut').addClass('animated zoomOut').fadeOut();
    });
  });
  $('.server-list > [data-server-id] button.btn').click(function(){
    profile.load_server($(this).parents('[data-server-id]'));
  });
  $('.group-list > [data-group-id] button.btn').click(function(){
    let server_id = $(this).parents('[data-group-id]').attr('data-server-id'),
        group_id =  $(this).parents('[data-group-id]').attr('data-group-id');
    profile.buy_group(group_id, server_id);
  });
});
