$(document).ready(function () {
    $(document).on('click', '.submit', {}, function (event) {
        loadFilteredData(this);
    });
});

function loadFilteredData(element)
{
    let hotelId = $('#hotels').val();
    let petFilter = $('#petFilter').val();
    let smokingFilter = $('#smokingFilter').val();

    $.ajax({
        url: $(element).attr('data-action'),
        type: 'GET',
        dataType: 'html',
        data: {
            'pageNumber': $(element).attr('data-page'),
            'hotelId': hotelId,
            'petFilter': petFilter,
            'smokingFilter': smokingFilter,
        },
        success: function (data, status) {
            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
