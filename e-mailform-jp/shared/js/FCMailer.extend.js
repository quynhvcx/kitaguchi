"use strict";

var _FCAsk_ = false;

$(document).ready(function() {
	$(":input[type='number'], .check-number").keydown(function(e) {
		var keyCode = e.which ? e.which : e.keyCode,
			_char_ = String.fromCharCode((keyCode >= 96 && keyCode <= 105) ? keyCode - 48 : keyCode),
			_allowKey_ = [8, 37, 38, 39, 40, 46], // Backspace, Left, Top, Right, Bottom, Delete
			_allowCtrl_ = [65, 89, 90], // A, Y, Z
			disabledIt = function() {
				e.returnValue = false;

				if (e.preventDefault) e.preventDefault();
			};

		if (!/[0-9]/.test(_char_)) {
			if (e.ctrlKey || e.metaKey) {
				if ($.inArray(keyCode, _allowCtrl_) < 0) disabledIt();
			}
			// else if ((keyCode < 96 || keyCode > 105) && $.inArray(keyCode, _allowKey_) < 0) disabledIt();
			else if ($.inArray(keyCode, _allowKey_) < 0) disabledIt();
		}
	});

	$("textarea.auto-resize").keydown(function() {
		$(this).css({
			height: "" // reset
		});

		setTimeout(function($input) {
			var _update_ = $input.get(0).scrollHeight,
				_height_ = parseInt($input.css("height"), 10);
				_minHeight_ = parseInt($input.css("min-height"), 10);

			_update_ += parseInt($input.css("border-top-width"), 10);
			_update_ += parseInt($input.css("border-bottom-width"), 10);

			if (_update_ > _height_  && _update_ > _minHeight_) {
				$input.css({
					height: _update_ // update
				});
			}
		}, 0, $(this));
	});

	$("body").on("keydown change", ":input", function() {
		_FCAsk_ = true;
	});
});

// progress step
var goStep = function($btn, step) {
		var $progress = $btn.parents(".fcform__main").siblings(".fcform__progress");

		$progress.find(".fcform__progress-step").removeClass("active"); // reset
		// $progress.find(".fcform__progress-" + step).addClass("active"); // active

		switch(step) {
			case "start":
				$progress.find(".fcform__progress-start").addClass("active");
				break;
			case "confirm":
				$progress.find(".fcform__progress-start").addClass("active");
				$progress.find(".fcform__progress-confirm").addClass("active");
				break;
			case "thanks":
				$progress.find(".fcform__progress-start").addClass("active");
				$progress.find(".fcform__progress-confirm").addClass("active");
				$progress.find(".fcform__progress-thanks").addClass("active");

				$progress.parents(".fcform").find(".fcform__note").hide();
				break;
			default:
				$progress.find(".fcform__progress-start").addClass("active");
		}
	},
	beforeReset = function($btn) {
		goStep($btn, "start");
	},
	afterReset = function($btn) {
		goStep($btn, "start");
	},
	beforeConfirm = function($btn) {
		goStep($btn, "start");
	},
	afterConfirm = function($btn) {
		goStep($btn, "confirm");
	},
	beforeBack = function($btn) {
		goStep($btn, "confirm");
	},
	afterBack = function($btn) {
		goStep($btn, "start");
	},
	beforeSend = function($btn) {
		goStep($btn, "confirm");
	},
	afterSend = function($btn) {
		_FCAsk_ = false;
		goStep($btn, "thanks");
	};

var _unload_ = function(e) {
		if (_FCAsk_) {
			var message = "Are you sure you want to leave?";

			e.preventDefault(); // cancel the event as stated by the standard
			e.returnValue = message; // chrome requires returnValue to be set

			return message;
		}
	};

if (navigator.userAgent.match(/iPad/i)|| navigator.userAgent.match(/iPhone/i)) {
	document.pagehide = _unload_;
	window.onpagehide = _unload_;
	window.addEventListener("pagehide", _unload_, false);
}
else {
	window.onunload = _unload_;
	window.onbeforeunload = _unload_;
	window.addEventListener("unload", _unload_, false);
	window.addEventListener("beforeunload", _unload_, false);
}