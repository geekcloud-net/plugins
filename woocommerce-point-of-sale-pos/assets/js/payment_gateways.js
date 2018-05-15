var goodScan;
var SwipeParserObj;
jQuery('document').ready(function($){
    if(wc_pos_params.cc_scanning == 'yes'){
        // Called on a good scan 
        goodScan = function (cardData) {

            var payment_method = $('.select_payment_method:checked:not(:disabled)').val();
            switch(payment_method){
                case 'stripe':
                    $('#stripe-card-number').val(cardData.account);
                    $('#stripe-card-expiry').val(cardData.exp_month+'/'+cardData.s_exp_year);
                    $('#stripe-card-cvc').focus();
                    break;
                case 'realex':
                    $('#realex_accountNumber').val(cardData.account);
                    $('#realex_cardType').val(cardData.c_type[1]);
                    $('#realex_expirationMonth').val(cardData.exp_month);
                    $('#realex_expirationYear').val(cardData.exp_year);
                    $('#realex_cvNumber').focus();
                    break;
                case 'braintree':
                    $('#braintree-cc-number').val(cardData.account);
                    $('#braintree-cc-exp-month').val(cardData.exp_month);
                    $('#braintree-cc-exp-year').val(cardData.exp_year);
                    $('#braintree-cc-cvv').focus();
                    break;
                case 'authorize_net_cim':
                    $('#authorize-net-cim-cc-number').val(cardData.account);
                    $('#authorize-net-cim-cc-exp-month').val(cardData.exp_month);
                    var year = parseInt(cardData.exp_year);
                    $('#authorize-net-cim-cc-exp-year').val(year);
                    break;
                case 'authorize_net_aim':
                    $('#wc-authorize-net-aim-account-number').val(cardData.account);
                    $('#wc-authorize-net-aim-exp-month').val(cardData.exp_month);
                    var year = parseInt(cardData.exp_year);
                    $('#wc-authorize-net-aim-exp-year').val(year);
                    $('#wc-authorize-net-aim-csc').focus();
                    break;
                case 'credomatic_aim':
                    $('#credomatic_aim-card-number').val(cardData.account);
                    $('#credomatic_aim-card-expiry').val(cardData.exp_month+'/'+cardData.s_exp_year);
                    $('#credomatic_aim-card-cvc').focus();
                    break;
                case 'paytrace':
                    $('#paytrace-card-number').val(cardData.account);
                    $('#paytrace-card-type').val(cardData.c_type[1]);
                    $('#paytrace-card-expiry').val(cardData.exp_month+'/'+cardData.s_exp_year);
                    $('#paytrace-card-cvc').focus();
                    break;
                case 'paypal_pro':
                    $('#paypal_pro-card-number').val(cardData.account);
                    $('#paypal_pro-card-expiry').val(cardData.exp_month+'/'+cardData.s_exp_year);
                    $('#paypal_pro-card-cvc').focus();
                    break;
                case 'paypal_pro_payflow':
                    $('#paypal_pro_payflow-card-number').val(cardData.account);
                    $('#paypal_pro_payflow-card-expiry').val(cardData.exp_month+'/'+cardData.s_exp_year);
                    $('#paypal_pro_payflow-card-cvc').focus();
                    break;
                case 'authorize_net_cim_credit_card':
                    $('#wc-authorize-net-cim-credit-card-account-number').val(cardData.account);
                    $('#wc-authorize-net-cim-credit-card-expiry').val(cardData.exp_month+'/'+cardData.s_exp_year);
                    $('#wc-authorize-net-cim-credit-card-csc').focus();
                    break;
            }
        };

        // Called on a bad scan
        var badScan = function () {
            alert('Card not recognized.');
        };

        function creditCardSwiperParser(rawData) {
            var swipeData = new SwipeParserObj(rawData);
            return swipeData.obj();
        };
        if( $('#order_payment_popup').length > 0 ){
            $.cardswipe({
                parser: creditCardSwiperParser,
                firstLineOnly: false,
                success: goodScan,
                error: badScan,
                debug: false,
                prefixCharacter: ';'
            });
        }
    }
})


function SwipeParserObj(strParse)
{
    ///////////////////////////////////////////////////////////////
    ///////////////////// member variables ////////////////////////
    this.input_trackdata_str = strParse;
    this.account_name = null;
    this.surname = null;
    this.firstname = null;
    this.acccount = null;
    this.exp_month = null;
    this.exp_year = null;
    this.track1 = null;
    this.track2 = null;
    this.hasTrack1 = false;
    this.hasTrack2 = false;
    /////////////////////////// end member fields /////////////////
    
    
    sTrackData = this.input_trackdata_str;     //--- Get the track data
    
  //-- Example: Track 1 & 2 Data
  //-- %B1234123412341234^CardUser/John^030510100000019301000000877000000?;1234123412341234=0305101193010877?
  //-- Key off of the presence of "^" and "="

  //-- Example: Track 1 Data Only
  //-- B1234123412341234^CardUser/John^030510100000019301000000877000000?
  //-- Key off of the presence of "^" but not "="

  //-- Example: Track 2 Data Only
  //-- 1234123412341234=0305101193010877?
  //-- Key off of the presence of "=" but not "^"

  if ( strParse != '' )
  {
    // alert(strParse);

    //--- Determine the presence of special characters
    nHasTrack1 = strParse.indexOf("^");
    nHasTrack2 = strParse.indexOf("=");

    //--- Set boolean values based off of character presence
    this.hasTrack1 = bHasTrack1 = false;
    this.hasTrack2 = bHasTrack2 = false;
    if (nHasTrack1 > 0) { this.hasTrack1 = bHasTrack1 = true; }
    if (nHasTrack2 > 0) { this.hasTrack2 = bHasTrack2 = true; }

    //--- Test messages
    // alert('nHasTrack1: ' + nHasTrack1 + ' nHasTrack2: ' + nHasTrack2);
    // alert('bHasTrack1: ' + bHasTrack1 + ' bHasTrack2: ' + bHasTrack2);    

    //--- Initialize
    bTrack1_2  = false;
    bTrack1    = false;
    bTrack2    = false;

    //--- Determine tracks present
    if (( bHasTrack1) && ( bHasTrack2)) { bTrack1_2 = true; }
    if (( bHasTrack1) && (!bHasTrack2)) { bTrack1   = true; }
    if ((!bHasTrack1) && ( bHasTrack2)) { bTrack2   = true; }

    //--- Test messages
    // alert('bTrack1_2: ' + bTrack1_2 + ' bTrack1: ' + bTrack1 + ' bTrack2: ' + bTrack2);

    //--- Initialize alert message on error
    bShowAlert = false;
    
    //-----------------------------------------------------------------------------    
    //--- Track 1 & 2 cards
    //--- Ex: B1234123412341234^CardUser/John^030510100000019301000000877000000?;1234123412341234=0305101193010877?
    //-----------------------------------------------------------------------------    
    if (bTrack1_2)
    { 
//      alert('Track 1 & 2 swipe');

      strCutUpSwipe = '' + strParse + ' ';
      arrayStrSwipe = new Array(4);
      arrayStrSwipe = strCutUpSwipe.split("^");
  
      var sAccountNumber, sName, sShipToName, sMonth, sYear;
  
      if ( arrayStrSwipe.length > 2 )
      {
        this.account           = stripAlpha( arrayStrSwipe[0].substring(1,arrayStrSwipe[0].length) );
        this.c_type            = detectCardType(this.account);
        this.account_name      = arrayStrSwipe[1];
        this.exp_month         = arrayStrSwipe[2].substring(2,4);
        this.exp_year          = '20' + arrayStrSwipe[2].substring(0,2); 
        this.short_exp_year    = arrayStrSwipe[2].substring(0,2); 

        
        
        //--- Different card swipe readers include or exclude the % in the front of the track data - when it's there, there are
        //---   problems with parsing on the part of credit cards processor - so strip it off
        if ( sTrackData.substring(0,1) == '%' ) {
            sTrackData = sTrackData.substring(1,sTrackData.length);
        }

        var track2sentinel = sTrackData.indexOf(";");
        if( track2sentinel != -1 ){
            this.track1 = sTrackData.substring(0, track2sentinel);
            this.track2 = sTrackData.substring(track2sentinel);
        }

        //--- parse name field into first/last names
        var nameDelim = this.account_name.indexOf("/");
        if( nameDelim != -1 ){
            this.surname = this.account_name.substring(0, nameDelim);
            this.firstname = this.account_name.substring(nameDelim+1);
        }
      }
      else  //--- for "if ( arrayStrSwipe.length > 2 )"
      { 
        bShowAlert = true;  //--- Error -- show alert message
      }
    }
    
    //-----------------------------------------------------------------------------
    //--- Track 1 only cards
    //--- Ex: B1234123412341234^CardUser/John^030510100000019301000000877000000?
    //-----------------------------------------------------------------------------    
    if (bTrack1)
    {
//      alert('Track 1 swipe');

      strCutUpSwipe = '' + strParse + ' ';
      arrayStrSwipe = new Array(4);
      arrayStrSwipe = strCutUpSwipe.split("^");
  
      var sAccountNumber, sName, sShipToName, sMonth, sYear;
  
      if ( arrayStrSwipe.length > 2 )
      {
        this.account = sAccountNumber = stripAlpha( arrayStrSwipe[0].substring( 1,arrayStrSwipe[0].length) );
        this.account_name = sName   = arrayStrSwipe[1];
        this.exp_month = sMonth = arrayStrSwipe[2].substring(2,4);
        this.exp_year = sYear   = '20' + arrayStrSwipe[2].substring(0,2); 
  
        
        //--- Different card swipe readers include or exclude the % in
        //--- the front of the track data - when it's there, there are
        //---   problems with parsing on the part of credit cards processor - so strip it off
        if ( sTrackData.substring(0,1) == '%' ) { 
            this.track1 = sTrackData = sTrackData.substring(1,sTrackData.length);
        }
  
        //--- Add track 2 data to the string for processing reasons
//        if (sTrackData.substring(sTrackData.length-1,1) != '?')  //--- Add a ? if not present
//        { sTrackData = sTrackData + '?'; }
        this.track2 = ';' + sAccountNumber + '=' + sYear.substring(2,4) + sMonth + '111111111111?';
        sTrackData = sTrackData + this.track2;
  
        //--- parse name field into first/last names
        var nameDelim = this.account_name.indexOf("/");
        if( nameDelim != -1 ){
            this.surname = this.account_name.substring(0, nameDelim);
            this.firstname = this.account_name.substring(nameDelim+1);
        }

      }
      else  //--- for "if ( arrayStrSwipe.length > 2 )"
      { 
        bShowAlert = true;  //--- Error -- show alert message
      }
    }
    
    //-----------------------------------------------------------------------------
    //--- Track 2 only cards
    //--- Ex: 1234123412341234=0305101193010877?
    //-----------------------------------------------------------------------------    
    if (bTrack2)
    {
//      alert('Track 2 swipe');
    
      nSeperator  = strParse.indexOf("=");
      sCardNumber = strParse.substring(1,nSeperator);
      sYear       = strParse.substr(nSeperator+1,2);
      sMonth      = strParse.substr(nSeperator+3,2);

      // alert(sCardNumber + ' -- ' + sMonth + '/' + sYear);

      this.account = sAccountNumber = stripAlpha(sCardNumber);
      this.exp_month = sMonth       = sMonth;
      this.exp_year = sYear         = '20' + sYear; 
        
      //--- Different card swipe readers include or exclude the % in the front of the track data - when it's there, 
      //---  there are problems with parsing on the part of credit cards processor - so strip it off
      if ( sTrackData.substring(0,1) == '%' ) {
        sTrackData = sTrackData.substring(1,sTrackData.length);
      }
  
    }
    
    //-----------------------------------------------------------------------------
    //--- No Track Match
    //-----------------------------------------------------------------------------    
    if (((!bTrack1_2) && (!bTrack1) && (!bTrack2)) || (bShowAlert))
    {
      //alert('Difficulty Reading Card Information.\n\nPlease Swipe Card Again.');
    }

//    alert('Track Data: ' + document.formFinal.trackdata.value);
    
    //document.formFinal.trackdata.value = replaceChars(document.formFinal.trackdata.value,';','');
    //document.formFinal.trackdata.value = replaceChars(document.formFinal.trackdata.value,'?','');

//    alert('Track Data: ' + document.formFinal.trackdata.value);

  } //--- end "if ( strParse != '' )"


    this.dump = function(){
        var s = "";
        var sep = "\r"; // line separator
        s += "Name: " + this.account_name + sep;
        s += "Surname: " + this.surname + sep;
        s += "first name: " + this.firstname + sep;
        s += "account: " + this.account + sep;
        s += "exp_month: " + this.exp_month + sep;
        s += "exp_year: " + this.exp_year + sep;
        s += "has track1: " + this.hasTrack1 + sep;
        s += "has track2: " + this.hasTrack2 + sep;
        s += "TRACK 1: " + this.track1 + sep;
        s += "TRACK 2: " + this.track2 + sep;
        s += "Raw Input Str: " + this.input_trackdata_str + sep;
        
        return s;
    }
    this.obj = function(){
        var data = {
            name       : this.account_name,
            surname    : this.surname,
            firstname  : this.firstname,
            account    : this.account,
            c_type     : this.c_type,
            exp_month  : this.exp_month,
            exp_year   : this.exp_year,
            s_exp_year : this.short_exp_year,
            hasTrack1  : this.hasTrack1,
            hasTrack2  : this.hasTrack2,
            track1     : this.track1,
            track2     : this.track2,
            trackdata_str : this.input_trackdata_str,
        };
        return data;
    }

    function stripAlpha(sInput){
        if( sInput == null )    return '';
        return sInput.replace(/[^0-9]/g, '');
    }

}
function detectCardType(number) {
    var re = {
        electron    : /^(4026|417500|4405|4508|4844|4913|4917)\d+$/,
        maestro     : /^(5018|5020|5038|5612|5893|6304|6759|6761|6762|6763|0604|6390)\d+$/,
        dankort     : /^(5019)\d+$/,
        interpayment: /^(636)\d+$/,
        unionpay    : /^(62|88)\d+$/,
        visa        : /^4[0-9]{12}(?:[0-9]{3})?$/,
        mastercard  : /^5[1-5][0-9]{14}$/,
        amex        : /^3[47][0-9]{13}$/,
        diners      : /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
        discover    : /^6(?:011|5[0-9]{2})[0-9]{12}$/,
        jcb         : /^(?:2131|1800|35\d{3})\d{11}$/,
        laser       : /^(6304|6706|6709|6771)[0-9]{12,15}$/, 
        switch_     : /^(4903|4905|4911|4936|6333|6759)[0-9]{12}|(4903|4905|4911|4936|6333|6759)[0-9]{14}|(4903|4905|4911|4936|6333|6759)[0-9]{15}|564182[0-9]{10}|564182[0-9]{12}|564182[0-9]{13}|633110[0-9]{10}|633110[0-9]{12}|633110[0-9]{13}$/,
    };
    if (re.electron.test(number)) {
        return ['ELECTRON','ELECTRON'];
    } else if (re.maestro.test(number)) {
        return ['MAESTRO','MAESTRO'];
    } else if (re.dankort.test(number)) {
        return ['DANKORT','DANKORT'];
    } else if (re.interpayment.test(number)) {
        return ['INTERPAYMENT', 'INTERPAYMENT'];
    } else if (re.unionpay.test(number)) {
        return ['UNIONPAY','UNIONPAY'];
    } else if (re.visa.test(number)) {
        return ['VISA','VISA'];
    } else if (re.mastercard.test(number)) {
        return ['MASTERCARD','MC'];
    } else if (re.amex.test(number)) {
        return ['AMEX','AMEX'];
    } else if (re.diners.test(number)) {
        return ['DINERS', 'DINERS'];
    } else if (re.discover.test(number)) {
        return ['DISCOVER','DISCOVER'];
    } else if (re.jcb.test(number)) {
        return ['JCB','JCB'];
    } else if (re.laser.test(number)) {
        return ['LASER','LASER'];
    } else if (re.switch_.test(number)) {
        return ['SWITCH','SWITCH'];
    } else {
        return undefined;
    }
}