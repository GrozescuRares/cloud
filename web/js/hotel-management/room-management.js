$(document).ready(function () {
    $('.filters-toggle').click();
    $(document).on('click', '#filter', {}, function (event) {
        filterData(event, this);
    });
    $(document).on('click', '.reset-filters', {}, function (event) {
        filterData(event, this);
    });
});

function filterData(event, element)
{
    event.preventDefault();
    let hotelId = $('#hotels').val();
    let petFilter = $('#petFilter').val();
    let smokingFilter = $('#smokingFilter').val();
    let resetFilters = $(element).attr('data-filter-reset');

    if (resetFilters) {
        petFilter = smokingFilter = "all";
        hotelId = $('#hotels option:first').val();
    }

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
            if (resetFilters) {
                $('#hotels').val(hotelId);
                $('#petFilter').val('Pets');
                $('#smokingFilter').val('Smoking');
                $('.selectpicker').selectpicker('refresh');
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
