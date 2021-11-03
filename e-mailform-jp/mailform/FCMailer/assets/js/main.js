/*
 * FCMailer - 2017
 *
 * ScriptName: main.js
 *
 * PackageName: FCMailer
 * Version: 3.5
 *
 * FoodConnection
 * http://foodconnection.jp/
 * http://foodconnection.vn/
 *
 * Email: trung.styles@gmail.com
 *
 */

$(document).ready(function() {
	if ($("#login").length > 0) $("#login").find("input[name=username]").focus();

	$("#login button").click(function() {
		var $login = $(this).parents("#login"),
			username = $login.find("input[name=username]").val();
			password = $login.find("input[name=password]").val();

		$.ajax({
			url: base_url + script_name + "?fc=login",
			type: "POST",
			dataType: "JSON",
			data: {
				username: username,
				password: password
			}, beforeSend: function() {
				$login.find(":input").prop("disabled", true);
			}, success: function(json) {
				$login.find(".notice").stop().slideUp(300, function() {
					// $login.find(".notice").empty();
					$login.find(".notice").text(json.messages);
					$login.find(".notice").removeAttr("style").hide();
					$login.find(".notice").removeClass("error success");
					$login.find(".notice").addClass(json.status);
				}).delay(300).slideDown(300, function() {
					$login.find(".notice").removeAttr("style").show();

					if (json.status == "success") {
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						if ($login.find("input[name=username]").val()) $login.find("input[name=password]").select().focus();
						else $login.find("input[name=username]").select().focus();
					}
				});

				$login.find(":input").prop("disabled", false);
			}
		});
	});

	$("#login input").keydown(function(e) {
		var keyCode = e.which ? e.which : e.keyCode;

		if (keyCode == 13) $(this).parents("#login").find("button").click(); // enter key is pressed
	});

	$("#header .logout").click(function() {
		var logout = confirm("Are you sure you want to logout?");

		if (logout) {
			$.get(base_url + script_name + "?fc=logout", function() {
				location.reload();
			});
		}
	});

	var timerNotice;
	$("#container").on("click", ".notice", function() {
		$(this).removeClass("active");
	}).on("click", ".col-click", function() {
		$(this).parents("tr").first().find(".messages").toggleClass("active");
	}).on("click", ".remove", function() {
		var $btn = $(this),
			$row = $btn.parents("tr").first();

		var removeIt = confirm("Are you sure to delete this item?");

		if (removeIt) {
			$.ajax({
				url: base_url + script_name + "?fc=remove",
				type: "POST",
				dataType: "JSON",
				data: {
					id: $row.attr("data")
				}, beforeSend: function() {
					$("#container .notice").removeClass("active");
					$btn.addClass("active");
				}, success: function(json) {
					if (json.status == "success") {
						if ($row.parent().children("tr").length <= 1) {
							$row.parents(".result").stop().fadeOut(300, function() {
								$(this).remove();

								$("#container .no-data").stop().delay(300).fadeIn(300, function() {
									$(this).removeAttr("style").addClass("active");
								});
							});
						} else {
							$row.stop().fadeOut(300, function() {
								$(this).remove();
							});
						}
					} else $btn.removeClass("active");

					$("#container .notice").removeClass("active");
					$("#container .notice").text(json.messages);
					$("#container .notice").addClass("active");

					clearTimeout(timerNotice);
					timerNotice = setTimeout(function() {
						$("#container .notice").removeClass("active");
					}, 3000);
				}
			});
		}
	});
});