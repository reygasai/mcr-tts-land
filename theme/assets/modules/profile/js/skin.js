let skinViewer = new skinview3d.SkinViewer({
	domElement: document.getElementById("profile-box-skin"),
	width: 300,
	height: 340,
	skinUrl: '/uploads/skins/default.png',
});

$.get($('.skin-block').attr('data-cape')).done(function () {
	skinViewer.capeUrl = $('.skin-block').attr('data-cape');
});

$.get($('.skin-block').attr('data-url')).done(function () {
	skinViewer.skinUrl = $('.skin-block').attr('data-url');
});

let control = skinview3d.createOrbitControls(skinViewer);
control.enableRotate = true;
control.enableZoom = false;
control.enablePan = false;
skinViewer.animation = new skinview3d.CompositeAnimation();

let walk = skinViewer.animation.add(skinview3d.WalkingAnimation);	