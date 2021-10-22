document.addEventListener("DOMContentLoaded",function(){
    $("input[name='payment-option']").change(function(){
        $('#cart-pittica-bankwirediscount').remove();
        var moduleName=$(this).attr('data-module-name');
        if(moduleName==='pitticabankwirediscount'){
            $('#cart-subtotal-products').after('<div class="cart-summary-line" id="cart-pittica-bankwirediscount"><span class="label">'+pittica_bankwirediscount_label+'</span><span class="value">-'+pittica_bankwirediscount_discount+'</span></div>');
            $('#js-checkout-summary .cart-summary-totals').replaceWith(pittica_bankwirediscount_totals);
            pittica_cart_lock=true;
        }else if(!moduleName.startsWith('pittica')&&!pittica_cart_lock){
            if(!pittica_cart_lock){
                $.ajax({
                    url:$('#js-checkout-summary').attr('data-refresh-url'),
                    type:"GET",
                    dataType:"json",
                    success:function(data){
                        $('#js-checkout-summary .cart-summary-totals').replaceWith(data.cart_summary_totals);
                    }
                });
            }
        }else{
            pittica_cart_lock=false;
        }
    })
});