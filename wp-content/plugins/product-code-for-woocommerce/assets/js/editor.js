(function($){
    var product_id
    var is_variant = 0
    $(document).ready( function(){

       /* var prev = '';

        $( ".variations_form" ).on( "woocommerce_variation_select_change", function () {
            var val = $(this).find('select').val();
            var className = '.if_' + val;
            $(className).removeClass('hidden');
            if( prev ){
                $(prev).addClass('hidden');
            }
            prev = className;
        } );*/
        product_id = $( 'input#product_id' ).val();
        product_id_second = $( 'input#product_id_second' ).val();
        
        check_product_code('first');
        check_product_code('second');
        $( ".variations_form" ).on( "hide_variation", function ( e, variation ) {
            if(PRODUCT_CODE.HIDE_EMPTY == "yes")
             $( '.wo_productcode , .wo_productcode_second' ).hide();
        } );   
        $( ".variations_form" ).on( "show_variation", function ( e, variation ) {
            product_id = variation.variation_id
            
            is_variant = 1 
            check_product_code('first');
            check_product_code('second');
        } );

        $( '.reset_variations' ).on( 'click', function() {
            $( '.stl_codenum' ).html( 'N/A' )
        })

    } );

    function check_product_code(product_code)
    {

        var stl_codenum = '.stl_codenum';

        if(product_code =="second"){
            stl_codenum = '.stl_codenum_second';
        }
        $.ajax({
            url : PRODUCT_CODE.ajax,
            data : { action : 'product_code', product_code_id : product_id, is_variant : is_variant,code_num:product_code },
            dataType : 'json',
            type : 'post',
            beforeSend : function() {
                //$( '.stl_codenum' ).html( '' )
            },
            success : function( response ) {
                if(response.data =="" && PRODUCT_CODE.HIDE_EMPTY == "yes"){
                    $( stl_codenum ).parent('span').hide();
                   // $( stl_codenum ).html( response.data );
                }else if(response.data==""){
                    $( stl_codenum ).html( "N/A" );
                }else if( response.data ) {
                    $( stl_codenum ).html( response.data );
                    $( stl_codenum ).parent('span').show();    
                    
                }
            }
        })
    }
})(jQuery);