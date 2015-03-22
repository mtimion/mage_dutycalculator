function changeServiceFee()
{
	new Ajax.Request(
		$('dc_delivery_duty_options').getAttribute('action'), {
			method: 'post',
			parameters: $('dc_delivery_duty_options').serialize(true),
			onLoading: function() {
				checkout.setLoadWaiting('review');
			},
			onSuccess: function(response) {
				checkout.setLoadWaiting(false);
				checkout.reloadProgressBlock('payment');
				checkout.reloadReviewBlock();
			},
			onFailure: checkout.ajaxFailure.bind(checkout)
	});
}

function changeServiceFeeForPayPalExpress()
{
	new Ajax.Request(
		$('dc_delivery_duty_options').getAttribute('action'), {
			method: 'post',
			parameters: $('dc_delivery_duty_options').serialize(true),
			onLoading: function() {
				$('review-please-wait').show();
				if (PayPalExpressAjax.formSubmit) {
					PayPalExpressAjax.formSubmit.disabled = 'disabled';
					PayPalExpressAjax.formSubmit.addClassName('no-checkout');
					PayPalExpressAjax.formSubmit.setStyle({opacity:.5});
				}
			},
			onSuccess: function(response) {
				window.location.reload();
			},
			onFailure: function() {
				$('review-please-wait').hide();
				if (PayPalExpressAjax.formSubmit) {
					PayPalExpressAjax.formSubmit.disabled = false;
					PayPalExpressAjax.formSubmit.removeClassName('no-checkout');
					PayPalExpressAjax.formSubmit.setStyle({opacity:1});
				}
			}
	});
}

function changeServiceFeeForMultishipping(select, url)
{
	new Ajax.Request(
		url, {
			method: 'post',
			parameters: {'delivery_duty_type' : select.value},
			onSuccess: function(response) {
				window.location.reload();
			}
	});
}