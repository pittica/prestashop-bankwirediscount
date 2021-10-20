var pittica_bankwirediscount_lock=false;
document.addEventListener("DOMContentLoaded",function(){
    $("input[name='payment-option']").change(function(){
        var moduleName=$(this).attr('data-module-name');
        if(moduleName==='pitticabankwirediscount'){
            $('#cart-subtotal-products').append('<div class="cart-summary-line" id="cart-pittica-bankwirediscount"><span class="label">'+pittica_bankwirediscount_label+'</span><span class="value">-'+pittica_bankwirediscount_discount+'</span></div>');
            $('#js-checkout-summary .cart-summary-totals').replaceWith(pittica_bankwirediscount_totals);
            pittica_bankwirediscount_lock=true;
        }else{
            if(pittica_bankwirediscount_lock){
                pittica_bankwirediscount_lock=false;
                $('#cart-pittica-bankwirediscount').remove();
                $.ajax({
                    url:$('#js-checkout-summary').attr('data-refresh-url'),
                    type:"GET",
                    dataType:"json",
                    success:function(data){
                        $('#js-checkout-summary .cart-summary-totals').replaceWith(data.cart_summary_totals);
                    }
                });
            }
        }
    })
});