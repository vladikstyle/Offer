$(document).ready(function() {

    var $btnStripe = $('.btn-stripe'),
        $btnPayPal = $('.btn-paypal'),
        $btnRobokassa = $('.btn-robokassa'),
        $paymentAdd = $('.payment-add'),
        $loader = $('.payment-loader'),
        $form = $('.payment-form');

    function getAmount() {
        return $('.credits-input:checked').data('amount');
    }

    $btnStripe.on('click', function(event) {
        $paymentAdd.addClass('hidden');
        $loader.removeClass('hidden');
        event.preventDefault();

        $.ajax({
            url: $form.data('stripe-create-session'),
            type: 'post',
            data: $form.serializeArray(),
            success: function(result) {
                var stripe = Stripe($btnStripe.data('key'));
                if (result.success) {
                    stripe.redirectToCheckout({
                        sessionId: result.sessionId
                    }).then(function (result) {
                        Messenger().post({
                            message: result.message,
                            type: "error"
                        });
                    });
                } else {
                    Messenger().post({
                        message: result.message,
                        type: "error"
                    });
                }
            },
            completed: function() {
                $paymentAdd.removeClass('hidden');
                $loader.addClass('hidden');
            }
        });

        return false;
    });

    $btnPayPal.on('click', function (event) {
        event.preventDefault();
        $form
            .attr('action', $form.data('action-paypal'))
            .append($('<input>').attr({ type: 'hidden', name: 'amount', value: getAmount() }))
            .submit();
        $paymentAdd.addClass('hidden');
        $loader.removeClass('hidden');
    });

    $btnRobokassa.on('click', function (event) {
        event.preventDefault();
        $form
            .attr('action', $form.data('action-robokassa'))
            .append($('<input>').attr({ type: 'hidden', name: 'amount', value: getAmount() }))
            .submit();
        $paymentAdd.addClass('hidden');
        $loader.removeClass('hidden');
    });

});
