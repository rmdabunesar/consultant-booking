(function ($) {
  const sellerId = TCO_DATA.sellerId;
  const publishableKey = TCO_DATA.publishableKey;

  $("#submit-btn").on("click", function () {
    const form = $(".appointment-form");
    const ccNo = $("#card_number").val().replace(/\s+/g, "");
    const cvv = $("#cvv").val();
    const expDate = $("#expiry_date").val();
    const [expMonth, expYearRaw] = expDate.split("/");
    let expYear = expYearRaw.length === 2 ? "20" + expYearRaw : expYearRaw;
    

    TCO.requestToken(
      function success(data) {
        const token = data.response.token.token;
        
        form.find("input[name='token']").val(token);
        form.submit();
      },
      function error(err) {
        console.error(err.errorMsg || err);
      },
      {
        sellerId: 255806282485,
        publishableKey: "ED7A3F13-F0BA-4967-9F01-097697F95FA4",
        ccNo: 4111111111111111,
        cvv: 123,
        expMonth: 12,
        expYear: 2030,
      }
    );
  });
})(jQuery);
