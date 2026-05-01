jQuery(document).ready(function ($) {
    let socialIndex = $('#social-repeater .social-row').length;
    let availabilityIndex = $('#availability-container .availability-row').length;

    $('#add-social').on('click', function () {
        $('#social-repeater').append(`
                    <div class="social-row">
                        <select name="consultant_socials[${socialIndex}][platform]">
                            <option value="fa-facebook-f">Facebook</option>
                            <option value="fa-linkedin-in">LinkedIn</option>
                            <option value="fa-instagram">Instagram</option>
                            <option value="fa-youtube">YouTube</option>
                        </select>
                        <input type="url" name="consultant_socials[${socialIndex}][url]" placeholder="https://..." />
                        <button type="button" class="remove-row">Remove</button>
                    </div>
                `);
        socialIndex++;
    });

    $('#add-availability').on('click', function () {
        $('#availability-container').append(`
                    <div class="availability-row">
                        <select name="consultant_availability[${availabilityIndex}][day]">
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                        <input type="time" name="consultant_availability[${availabilityIndex}][from]" />
                        <input type="time" name="consultant_availability[${availabilityIndex}][to]" />
                        <button type="button" class="remove-row">Remove</button>
                    </div>
                `);
        availabilityIndex++;
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('.social-row, .availability-row').remove();
    });
});