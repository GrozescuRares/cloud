$(document).ready(function () {

   $('#hotels').change(function () {
       let hotelId = $('#hotels').val();
       $.ajax({
           url:        window.href,
           type:       'POST',
           dataType:   'json',
           data: {'hotelId': hotelId},
           async:      true,

           success: function(data, status) {
              console.log(data);
           },
           error : function(xhr, textStatus, errorThrown) {
               alert('Ajax request failed.');
           }
       });

   });
});
