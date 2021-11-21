$(document).ready(() => {
	$('[data-action=check-all]').click(function() {
		$(this).parents('table').find('input[type=checkbox]').prop('checked', $(this).prop('checked'));
	});
	$('[data-action=remove]').click(function(){
		let select = $('input[type=checkbox]:checked'),
				form = select.parents('form');
		if (select.length !== 1) return app.toast('Ошибка', 'Для изменения необходимо выбрать только один элемент');
		iziToast.show({
      title: 'Уверен?',
			theme: 'dark',
			timeout: false,
      close: false,
      message: $(this).attr('data-text'),
      position: 'center',
      buttons: [
        ['<button>Да</button>', function (instance, toast) {
					form.submit();
        }, true],
				['<button>Нет</button>', function (instance, toast) {
					iziToast.hide({
						ransitionOut: 'fadeOutUp'
					}, toast);
				}]
      ]
    });
		return false;
	});
	$('[data-action=edit]').click(function(){
		let elem = $('input[type=checkbox]:checked');
		if (elem.length !== 1) return app.toast('Ошибка', 'Для изменения необходимо выбрать только один элемент');
		location.href = $(this).attr('data-link') + elem.val();
	});
	$('[data-action=search]').click(()=> {
		let search = $('.form-control[data-target=search-input]').val();
		if (search.length <= 0) return app.toast('Ошибка', 'Введите данные для поиска');
		app.changeUrlParam({search: search});
	});
	$('#sidebarToggle').click(() => {
		$('body').toggleClass('sb-sidenav-toggled');
	});
});
