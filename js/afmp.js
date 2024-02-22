/**
©PICTAU	2018, OSCAR REY TAJES
Modified/forked from codyhouse: https://github.com/CodyHouse/morphing-modal-window
**/

(function($){
	var customZindex = 100000;


	$('.afmp').on('click tap',function(e){
		e.preventDefault();
		var $el = $(this); // el botón
		var $mContent = $($el.attr('href')); // el contenido de la modal

		if ($mContent.length == 0) {console.log('ATENCIÓN!!\nid de modal no encontrado en el HREF del link'); return} // si no encontramos el contenido de la modal a mostrar...



		// dimensiones del botón para asignarlo a su style para que transition tenga un origen
		var dim = {
			'height': $el.outerHeight(),
			'width': $el.outerWidth(),
			'borderRadius' : $el.css('border-radius')
		}


		var bodyYpos = $(window).scrollTop();

		$el.css({'width': dim.width + 'px'});


		$('body').toggleClass('overflow-hidden', true);
		$('html').toggleClass('overflow-hidden',true);






		// creamos el background en el body del dom
		$('body .pct-modal-bg').length ? '' : $('body').append('<span class="pct-modal-bg"></span>');
		var $mBg = $('body .pct-modal-bg');

		//disabling scroll for mobile devices

		$mBg.on('touchstart touchmove touchend tap',function(e){
			e.preventDefault();
		});



		// PERSONALIZATION
		var modalColor = ($el.attr('data-modal-color')) ? $el.attr('data-modal-color') : '#34383C';
		var modalShape = ($el.attr('data-modal-shape')) ? $el.attr('data-modal-shape') : 'circle';

		//Movemos el contenido de la modal como sibling del botón
		$('body').append($mContent);

		//añadimos UI botón close
		//($mContent.find('.afmp-modal-close').length) ? '' :  $mContent.append('<a href="#" class="afmp-modal-close">Close</a>');

		($('.afmp-modal-close').length) ? '' :  $('body').append('<a href="" class="afmp-modal-close">Close</a>');

		$mClose = $('.afmp-modal-close');



		//..y la zona gradiente al final de la modal
		setModalFooter($mContent,modalColor);

		//dimensiones iniciales del background
		$mBg.css({
			'width' : dim.height,
			'height' : dim.height,
			'border-radius': (modalShape == 'circle') ? '50%' : '0px'
		});

		setModalColor(modalColor);


		//añadimos clase al botón para animación de cerrado
		$mBg.css({
			'top' : $el.offset().top - $(window).scrollTop() + 'px',
			'left' : $el.offset().left  +  'px',
			'width' :  + dim.width + 'px',
			'height' :  + dim.height + 'px',
			'border-radius' : $el.css('border-radius'),

		});


		$mBg.addClass('is-visible').one('webkitAnimationEnd oanimationend msAnimationEnd animationend otransitionend oTransitionEnd msTransitionEnd transitionend', function(e){
			//ocultamos boton launcher
			$el.css({'visibility': 'hidden','opacity' : '0'});
			implode($mBg,dim,true);
		});




		function getScale(bg) {
			var bgRadius = bg.width()/2,
				left = bg.offset().left,
				top = bg.offset().top - $(window).scrollTop(),
				scale = scaleValue(top, left, bgRadius, $(window).height(), $(window).width());

			bg.velocity({
				top:bg.position().top,
				left: bg.position().left,
				translateX: 0,
			}, 0);
			return scale;
		}

		function animLayer(layer, scaleVal, bool) {
			layer.velocity({ scale: scaleVal }, (bool) ? 400 : 400, function(){

				if(bool) {
					$mContent.addClass('modal-is-visible').end().off('otransitionend oTransitionEnd msTransitionEnd transitionend');
					$('body').addClass('modal-is-visible');
				}
				else{
					implode($mBg,dim,false);
				}

			});
		}

		function implode(layer,dim,bool){
			// Background forma botón a cuadrado/circulo (bool = true) ó círculo/cuadrado a froma botón (bool = false)
			(bool) ? layer.velocity({ width: dim.height +'px', borderRadius: dim.borderRadius }, 300, function(){
				animLayer($mBg, getScale($mBg), true);
			}) : layer.velocity({ width: dim.width +'px', borderRadius: dim.borderRadius, opacity: '0' }, 400, function(){

				$el.removeAttr('style');
				//debemos dar margen para que termine la animación de velocity ??? revisar
				setTimeout(function(){
				$el.removeAttr('style');
					layer.removeAttr('style');
					layer.removeClass('is-visible');
					$('body').toggleClass('overflow-hidden', false);
					$('html').toggleClass('overflow-hidden',false);
				},300);
			});
		}


		//trigger the animation - close modal window
		$('.afmp-modal-close').on('click tap', function(e){
			e.preventDefault();
			window.afmp_hideModal();
		});


		window.afmp_hideModal = function() {
			$('body').removeClass('modal-is-visible');

			$('.afmp-content.modal-is-visible').removeClass('modal-is-visible').one('otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
				animLayer($mBg, 1, false);
			});
			//if browser doesn't support transitions...
			if($mBg.parents('.no-csstransitions').length > 0 ) animLayer($mBg, 1, false);
		}


		function setModalFooter(modal,c){
			var hexc = hex2rgb(c);

			if(!modal.find('.afmp-modal-footer').length){
				modal.append('<div class="afmp-modal-footer"></div>');
				modal.find('.afmp-modal-footer').css({
					'position' : 'fixed',
					'left' : '0',
					'bottom' : '0',
					'width' : '100%',
					'height' : '6rem',
					'pointer-events' : 'none',
					'background' : 'transparent',
					'background' : 'linear-gradient(to top, '+ c +' 30%, rgba('+ hexc.r +', '+ hexc.g +', '+ hexc.b +', 0))',
				});
				return true;
			}
			else {
				return null;
			}


		}

		function setModalColor(c) {


			$mBg.css({
				'background-color' : c,
			});
		}


		$(window).on('resize', function(){
			//on window resize - update cover layer dimention and position
			if($('.pct-modal-bg.is-visible').length > 0) window.requestAnimationFrame(updateLayer);
		});


		function updateLayer() {
			var layer = $('.pct-modal-bg.is-visible'),
				layerRadius = layer.width()/2,
				layerTop = $el.offset().top + layerRadius - $(window).scrollTop(),
				layerLeft = $el.offset().left + layerRadius,
				scale = scaleValue(layerTop, layerLeft, layerRadius, $(window).height(), $(window).width());

			layer.velocity({
				top: layerTop - layerRadius,
				left: layerLeft - layerRadius,
				scale: scale,
			}, 0);
		}



	});


	// Hex to RGB
	function hex2rgb(hex) {
	    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	    return result ? {
	        r: parseInt(result[1], 16),
	        g: parseInt(result[2], 16),
	        b: parseInt(result[3], 16),
	        rgb: parseInt(result[1], 16) + ", " + parseInt(result[2], 16) + ", " + parseInt(result[3], 16)
	    } : null;
	}

	function scaleValue( topValue, leftValue, radiusValue, windowW, windowH) {
		var maxDistHor = ( leftValue > windowW/2) ? leftValue : (windowW - leftValue),
			maxDistVert = ( topValue > windowH/2) ? topValue : (windowH - topValue);
		return Math.ceil(Math.sqrt( Math.pow(maxDistHor*1.3, 2) + Math.pow(maxDistVert*1.3, 2) )/radiusValue);
	}




})(jQuery);