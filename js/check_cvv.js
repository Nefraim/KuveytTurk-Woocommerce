function validate_cvv(str) {

  if(jQuery("input#place_order").attr("disabled") != "disabled") {
    jQuery("input#place_order").attr("disabled", "disabled");
    jQuery("input#place_order").val("CCV Kontrol Ediniz");
  }
  
  for(var i = 0; i < str.length; i ++) {
    if(isNaN(parseInt(str.charAt(i), 10)) || i > 3) return;
  }
  
  if(i > 2) {
    jQuery("input#place_order").removeAttr("disabled");
    jQuery("input#place_order").val("SİPARİŞİ ONAYLA");
  }

}