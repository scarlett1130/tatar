
var img_path = "", // images direcorty
	windowWidth, // window width
	zoomvalue = 1, // map zoom value
	zoomStepValue = .2, //zoom value change per click
	_whatsapp= "966558693956";

// =====================================
/**
* @description : load jQuery if not loaded
* and startup init
*/
if (!window.jQuery) {
	script = document.createElement('script');
	document.head.appendChild(script);
	script.type = 'text/javascript';
	script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
	script.onload = function () {
		startUp()
	}
} else {
	startUp();
}
// =====================================


/**
* @description : startup function
*/
function startUp() {

jQuery.noConflict();

	
	jQuery("#ce"). remove ();
	jQuery("body") . append ( "<div id='ce'></div>");


	windowWidth = jQuery(window).width();
	basename = getBaseName();

		jQuery(".copyright").append("<a href='https://api.whatsapp.com/send/?phone=%2B966558693956&text&app_absent=0' class='whats-app'></a>");
	
	if ( basename == 'login.php') {

	}
	// console.log(basename);
	if (basename == 'login.php' || basename == 'anmelden.php') {
		jQuery("#footer").addClass('isrelative');

		if ( windowWidth > 999 ){
			var o= `<a href="#" class="large-text" title="اختار العالم" onclick="return worldSelect();" id="worldMenuSelect">
			▽ اختار العالم
			</a>`;
			jQuery("#header") . html ( o );
			jQuery("#side_navi #worldMenuSelect") .remove ()
		}
		if (parseInt(windowWidth) <= 999) {
			var els = jQuery("#side_navi a");
			jQuery("#mtop").empty().append("<div class='flex-mtop'></div>");
			jQuery.each(els, function (i, elm) {
				var _text = jQuery(elm).text();
				var _href = jQuery(elm).attr('href');
				var _onlcick = (jQuery(elm).attr('onclick') ?
												" onclick=\""+jQuery(elm).attr('onclick')+"\"" :
												""
											 )
				if (jQuery(elm).text()) {
					jQuery(".flex-mtop").append("<div>" +
						"<a href='" + _href + "'"+_onlcick+">" + _text + "</a>" +
						"</div>");

				}
			});
		}
	} else {
		if (parseInt(windowWidth) <= 999) {
			jQuery.each(jQuery("#mtop a"), function (i, el) {
				var _href = jQuery(el).attr('href');
				if (_href == basename) jQuery(el).addClass('active');
			});
		}


		if (basename == 'map.php') {
			zoomBtns();
		}

		if ( basename == 'village1.php' && parseInt(windowWidth) <= 999 ) {
			village_1();
		}
		if ( basename == 'village2.php' && parseInt(windowWidth) <= 999 ) {
			village_2();
		}
		if ( basename == 'village3.php' && parseInt(windowWidth) <= 999 ) {
			village_3();
		}
		if ( basename == 'plus.php' && parseInt(windowWidth) <= 999 ) {
			plus_func();
		}
		if ( basename == 'farm.php' && parseInt(windowWidth) <= 999 ) {
			farm();
		}

		if (parseInt(windowWidth) <= 999) {
			jQuery("#mtop").addClass("user");
			jQuery("body").addClass("user");
			var custom_menu = '<a href="alliance.php?chat" title="شات التحالف">' +
				'<img src="' + img_path + 'images/chat.red.svg" alt="chat">' +
				'</a>' +
				'<a href="index.php" title="الرئيسية">' +
				'<img src="' + img_path + 'images/main.svg" alt="chat">' +
				'</a>' +
				'<a href="redseahost.php?t=3" title="شروحات اللعبة">' +
				'<img src="' + img_path + 'images/explain.svg" alt="explain">' +
				'</a>' +
				'<a href="msg.php?uid=1" title="مراسلة الإدارة">' +
				'<img src="' + img_path + 'images/MsgAdmin.svg" alt="MsgAdmin">' +
				'</a>' +
				'<a href="profile.php" title="العضوية">' +
				'<img src="' + img_path + 'images/profile.svg" alt="profile">' +
				'</a>' +
				'<a href="#" title="القائمة" onclick="return createMobileNav();">' +
				'<img src="' + img_path + 'images/menu-svgrepo-com.svg" alt="main">' +
				'</a>';
			jQuery("#dynamic_header").append("<div id='dynamic_header__menu'></div>")
			jQuery("#dynamic_header__menu").html(custom_menu);
			jQuery("#showNavBtn") . click ( function () {
				jQuery('#navItems') . show();
			} );
		}
	}

	model2();
}
// =====================================
function worldSelect() {
	jQuery.ajax({
		url: "worlds-list.php",
		success: function ( ret ) {
			// console.log ( ret ) ;
			if ( ! jQuery("#worldsList") . length ) {
				jQuery("body") . append (ret);
				jQuery("#worldsList .closer") . click ( function () {
					jQuery("#worldsList") . hide ();
					return false;
				} );
			}
			jQuery("#worldsList") . show ();
		},
		fail: function ( ret ) {
			alert ( 'تأكد من اتصالك بالانترنت' )
			console.log ( ret )

		},
		error: function ( ret ) {
			alert ( 'تأكد من اتصالك بالانترنت' )
			console.log ( ret )

		}
	});
	return false;
}
// =====================================
function model2() {
	// console.log ("model2 function") ;
	jQuery("#n8") . after (`<a href="plus.php" class="banklink"></a>`) ;

	if ( jQuery("img.time_of_day.night").length ) {
		jQuery("#ltimeWrap") . addClass ('is_night');
	}

	if ( parseInt(windowWidth) <= 999 ) {
		if ( jQuery("#mobileSelectMenu") .length ) {
			if ( jQuery("#selectMenuContainer").length ){
				jQuery("#selectMenuContainer").remove();
			}
			jQuery ( "#mid") .before ( "<div id='selectMenuContainer'></div>" );
			var navOption = jQuery("#mobileSelectMenu option");
			var _navlinks=[`<li id="closeMobileNav">
					<a href="#">&#10006;</a>
			</li>`];
			jQuery.each ( navOption , function (i,op){
				var _text= jQuery(op).text();
				var _href= jQuery(op).attr('value');
            _navlinks.push("<li><a href=\""+_href+"\">"+_text+"</a></li>");
				// console.log (_text, _href);
			})
			/*jQuery("#selectMenuContainer").html (`<div id="navShowContainer">
					<a href="#" id="toggleMenu" class="fa fa-bars"> القائمة </a>
				</div>`)*/
			jQuery("body").append(`
				<div id="navItems">
					<ul>`+
						_navlinks.join("")+
					`</ul>
			</div>`);

			/*jQuery("#toggleMenu") . click ( function(){
				jQuery("#navItems") . show();
				return false;
			} );*/

			jQuery("#closeMobileNav a"). click( function(){
				jQuery("#navItems") . hide();
				return false
			} )
		}
	}

}



// =====================================
function createMobileNav() {
	jQuery("#navItems") . show();
	return false;
}

// =====================================


/**
* @description : get current base name
*/
function getBaseName() {
	return (location.href.split('/').pop().split('#')[0].split('?')[0]).replace("/\/", '');
}


// =====================================
/**
* @description : zoom in and aout
*/
function zoomBtns() {
	// console.log("sss");
	if ( location.href.match(/map\.php\?l/) ) {
		jQuery('head') . append('<meta name="viewport" content="width=1006" />')
		jQuery("body") . addClass ('resetMapSizes');

		var el = jQuery("#mbig");
		jQuery("#mbig").before(`<div class="row" id="map-zoom-btns">
			<a href="#" class="fa fa-plus"></a>
			<a href="#" class="fa fa-minus"></a>
			<div id="langskape-msg">ﻷفضل نتيجة قم بتدوير بتدوير جهازك</div>
		</div>`);

		jQuery("#map-zoom-btns a.fa-plus").click(function () {
			zoomvalue += zoomStepValue;
			el.css('zoom', zoomvalue);
			console.log(zoomvalue);
			return false;
		})
		jQuery("#map-zoom-btns a.fa-minus").click(function () {
			zoomvalue -= zoomStepValue
			if (zoomvalue < 1) {
				zoomvalue = 1;
			}
			el.css('zoom', zoomvalue);
			console.log(zoomvalue);
			return false;
		})
	}
}


// =====================================
/**
* @description : handles village 1 functions
*
*/
function village_1() {
	var _o=[];
	jQuery. each ( jQuery("#village_map img.reslevel"), function ( r, el ) {
		var cs =  jQuery(el).attr('class');
		var ref= cs.match(/rf([0-9]{1,2})/)
		var level= cs.match(/level([0-9]{1,2})/)
		_o.push(ref[1]+"="+level[1])
		// console.log( cs, ref[1], level[1] );
	})
	var iframeSrc= img_path +"iframe.php?"+ (_o.join("&"));
	jQuery("map") .
		after("<div id='map-svg-container'><iframe id='village1_map' src='"+iframeSrc+"'></iframe>")
		. remove();

	jQuery("div.village1 div.f3") . css ('background-image', 'none');
}


function map_loaded() {
	// console.log("loaded");
	window.addEventListener("load", function() {
		var svgObject = document.getElementById('village1_map').contentDocument;
		var element = svgObject.getElementById('map_id_2');
		var y = parseFloat(element.getAttributeNS(null, 'y'));
		// console.log(y);
	});


}

function village_2() {
	jQuery("body") . addClass('village_2');
	jQuery("#village_map") . after (`<div id="village_2_lswitch">
	<a href="#" onclick="toggleLevels(); return false;"><img src="ftd-style/default/img/g/s/glvlp.gif"></a>
	</div>`);
}

function village_3() {
	jQuery("body") . addClass('village_3');
}

function plus_func() {
	jQuery("body") . addClass('plus_body');
}


function farm () {
	jQuery("body") . addClass('farm_body');
}