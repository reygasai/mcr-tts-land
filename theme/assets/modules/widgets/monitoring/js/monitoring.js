'use strict';

let monitoring = {
  slots: 0,
  online: 0,
  parse_online: () => {
    $('.servers-box > .row > [class*="col-md-"]').each(function(){
      if($(this).attr('data-server-status') !== 'online') return;
      monitoring.slots += parseInt($(this).attr('data-server-slots'));
      monitoring.online += parseInt($(this).attr('data-server-online'));
    });
  },
  update_all_online: function() {
    let all_persents = Math.floor((this.online/this.slots)*100);
    $('.online-box > p > .online').html(this.online);
    $('.online-box > p > .max').html(this.slots);
    $('.server-status > .data-stats > span').html(all_persents + '%');
    $('.server-status > .progress > .progress-bar').css('width', all_persents + '%');
  }
};

$(document).ready(() => {
  if ($('.servers-box > .row > *').length % 2 === 1)  $('.servers-box > .row > *').removeClass('col-md-6').addClass('col-md-12') 
  monitoring.parse_online();
  monitoring.update_all_online();
});
