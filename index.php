<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Document</title>
    <style>
    body, input { font-family: Arial, serif; font-size: 16px; }
    input, button { padding: 10px; }
    .result, .scheme { margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <form id='card'>
        <input type='text' class='credit_card'>
    </form>

    <div class='result'></div> 
    <div class='scheme'></div> 
    
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
    <script>

    // Con API

    $(document).ready(function(){
        verify();
    });
    
    function verify(){
        var card = {
            scheme: '',
            type: '',
            api: {
                can_check: true,
                last_check: new Date()
            }
        }

        $('input.credit_card').bind('input', function(){
            var number = $(this).val();
            reset_result();

            if (!isNaN(number) && number.length >= 4){
                // Can check with regex
                card.scheme = detect_scheme(number);
                $('.scheme').text(card.scheme);

                if (number.length >= 6){
                    // Can check with API
                    if (!card.scheme){
                        // Don't recognized scheme, so use API for that too 
                        detect_type(number, true);
                    }
                    else {
                        detect_type(number, false);
                    }
                }
            }
        });

        // Detect if it's CREDIT or DEBIT
        function detect_type(n, search_scheme){
            var now = new Date(),
                dif = now-card.api.last_check < 500 ? 500 : 0;
            
            if (card.api.can_check){
                setTimeout(function(){
                    ajax(n);
                }, dif);
            }

            function ajax(n){
                card.api.can_check = false;

                $.ajax({
                    url: 'https://lookup.binlist.net/'+n,
                    dataType: 'json',
                    success: function(data){
                        card.api.can_check = true;

                        card.type = data.type;
                        show_type(data.type);

                        if (search_scheme){
                            card.scheme = data.scheme;
                            show_scheme(data.scheme);
                        }
                    },
                    error: function(jqXHR, textStatus, error){
                        console.log('error: '+error);
                        setTimeout(function(){
                            ajax();
                        }, 1000);
                    }
                });
            }

            function show_type(type){
                var type = type == 'DEBIT' ? 'Debito' : 'Crédito';
                $('.result').text(type);
            }

            function show_scheme(scheme){
                $('.scheme').text(scheme);
            }
        }

        // Detect if it's VISA, MASTERCARD, MAESTRO, ...
        function detect_scheme(number){
            // Visa
            var re = new RegExp('^4');
            if (number.match(re) != null)
                return 'Visa';

            // Mastercard
            re = new RegExp('^5[1-5]');
            if (number.match(re) != null)
                return 'Mastercard';

            // AMEX
            re = new RegExp('^3[47]');
            if (number.match(re) != null)
                return 'AMEX';

            // Discover
            re = new RegExp('^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)');
            if (number.match(re) != null)
                return 'Discover';

            // Diners
            re = new RegExp('^36');
            if (number.match(re) != null)
                return 'Diners';

            // Diners - Carte Blanche
            re = new RegExp('^30[0-5]');
            if (number.match(re) != null)
                return 'Diners - Carte Blanche';

            // JCB
            re = new RegExp('^35(2[89]|[3-8][0-9])');
            if (number.match(re) != null)
                return 'JCB';

            // Visa Electron
            re = new RegExp('^(4026|417500|4508|4844|491(3|7))');
            if (number.match(re) != null)
                return 'Visa Electron';

            return undefined;
        } 

        // Reset result
        function reset_result(){
            $('.result').text('');
            $('.scheme').text('');
        }
    }

        

    </script>
</body>
</html>