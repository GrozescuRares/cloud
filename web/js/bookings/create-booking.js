$(document).ready(function () {
    $(document).on('change', '.form-control', {}, function (event) {
        searchHotelByReservationDate(this);
    });
});

function searchHotelByReservationDate(element)
{
    let filed = true;
    $('.form-control').each(function () {
        if (!this.valueAsDate) {
            filed = false;

            return false;
        }
    });

    if (filed) {
        let startDate = $('#appbundle_reservationDto_startDate').val();
        let endDate = $('#appbundle_reservationDto_endDate').val();

        $.ajax({
            url: $('#appbundle_reservationDto_endDate').attr('data-action'),
            type: 'GET',
            dataType: 'html',
            data: {
                'startDate': startDate,
                'endDate' : endDate,
            },
            async: true,

            success: function (data, status) {
                $('.content').html(data);
                $('.selectpicker').selectpicker('refresh');
                console.log(data);
            },
            error: function (xhr, textStatus, errorThrown) {
                alert('Ajax request failed.')
            }
        });
    }
}
