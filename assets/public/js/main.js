(function ($) {
  "use strict";

  var paged = 1;
  $("#load-more").on("click", function () {
    paged++;

    $.ajax({
      url: consultantBooking.ajax_url,
      method: "GET",
      data: {
        action: "cb_load_more",
        paged: paged,
      },
      success: function (response) {
        $(".list").append(response);
      },
      error: function (error) {
        console.log(error);
      },
    });
  });

  $("#consultant-search-form").on("submit", function (e) {
    e.preventDefault();

    var searchTerm = $("#consultant-search-input").val();

    $.ajax({
      url: consultantBooking.ajax_url,
      type: "POST",
      data: {
        action: "cb_consultant_search",
        s: searchTerm,
        nonce: $('input[name="nonce"]').val(),
      },
      beforeSend: function () {
        $("#consultant-search-results").html("<p>Searching...</p>");
      },
      success: function (response) {
        $("#consultant-search-results").html(response);
      },
      error: function () {
        $("#consultant-search-results").html("<p>Not found, try again.</p>");
      },
    });
  });

  $("#payment_method").on("change", function () {
    if (this.value === "online") {
      $(".card-info").html(`
        <label for="card_number">Card Number:</label>
        <input type="text" id="card_number" name="card_number" placeholder="4111111111111111" required>
        <br>
        <div class="group-row">
          <div class="column">  
              <label for="expiry_date">Expiry Date:</label>
              <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
          </div>
          <div class="column"> 
              <label for="cvv">CVV:</label>
              <input type="text" id="cvv" name="cvv" placeholder="123" required>
          </div>
        </div>
        `);
    } else {
      $(".card-info").html("");
    }
  });
})(jQuery);
