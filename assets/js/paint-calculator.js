jQuery(document).ready(function ($) {
    // 1) Show the form on clicking the trigger link
    $(".calc-trigger").on("click", function (e) {
        e.preventDefault();
        $(".calc-form").slideDown();
        $(this).hide();
    });

    // 2) Close the form
    $(".close-form").on("click", function () {
        $(".calc-form").slideUp();
        $(".calc-trigger").show();
    });

    // 3) Enable/Disable fields once a finish is selected
    $(".paint-finish").on("change", function () {
        const isSelected = $(this).val().trim() !== "";
        $(".square-meters, .main-wall-width, .main-wall-height, .wall-width, .wall-height, .ceiling-width, .ceiling-length")
            .prop("disabled", !isSelected);
        $(".calculate-btn").prop("disabled", !isSelected);
    });

    // 4) Accordion logic for ceiling
    $(".accordion-header").on("click", function () {
        const $content = $(this).next(".accordion-content");
        const $icon = $(this).find(".accordion-icon");
        $content.slideToggle(200, function () {
            $icon.text($content.is(":visible") ? "−" : "+");
        });
    });

    // 5) Mutual exclusion logic for sections
    function clearSection1() {
        $(".square-meters").val(""); // Clear input
    }

    function clearSection2And3Inputs() {
        $(".main-wall-width, .main-wall-height").val("");
        $(".wall-width, .wall-height").val("");
    }

    $(".square-meters").on("input", function () {
        if ($(this).val().trim() !== "") {
            clearSection2And3Inputs();
        }
    });

    $(".main-wall-width, .main-wall-height, .wall-width, .wall-height").on("input", function () {
        clearSection1();
    });

    // 6) Expand/Collapse Additional Walls Section
    $(".toggle-walls").on("click", function () {
        const $wrapper = $(".dynamic-walls-wrapper");
        const $icon = $(this).find(".toggle-icon");

        $wrapper.slideToggle(300, function () {
            $icon.text($wrapper.is(":visible") ? "−" : "+");
        });
    });

    // 7) Clear all wall inputs on clicking ".wall-empty" icon
    $(document).on("click", ".wall-empty", function () {
        // Clear all wall inputs except the first one
        $(".additional-wall .wall-width, .additional-wall .wall-height").val("");
        
        // Hide all additional walls except the first one
        $(".additional-wall").not(".wall-1").slideUp();
    });

    // 8) Prevent input values from being negative
    $(".square-meters, .main-wall-width, .main-wall-height, .wall-width, .wall-height, .ceiling-width, .ceiling-length").on("input", function () {
        if ($(this).val() < 0) {
            $(this).val(0); // Reset to 0 if a negative value is entered
        }
    });

    // 9) Show next wall automatically if the current wall is fully filled
    function checkAndShowNextWall(wallIndex) {
        const widthVal = parseFloat($(`.wall-${wallIndex} .wall-width`).val()) || 0;
        const heightVal = parseFloat($(`.wall-${wallIndex} .wall-height`).val()) || 0;

        if (widthVal > 0 && heightVal > 0 && wallIndex < 4) {
            $(`.wall-${wallIndex + 1}`).slideDown();
        }
    }

    for (let i = 1; i <= 4; i++) {
        $(`.wall-${i} input`).on("input", function () {
            checkAndShowNextWall(i);
        });
    }

    // 10) Add close (×) button for each additional wall, EXCEPT the first one
    $(".additional-wall").each(function (index) {
        const wallIndex = index + 1; // Wall 1 is always shown
        if (wallIndex > 1) {
            $(this).append(`<span class="remove-wall" data-wall-index="${wallIndex}">×</span>`);
        }
    });

    // 11) Remove wall functionality (but keep the first wall visible)
    $(document).on("click", ".remove-wall", function () {
        const wallIndex = $(this).data("wall-index");
        if (wallIndex > 1) {
            $(`.wall-${wallIndex} .wall-width, .wall-${wallIndex} .wall-height`).val(""); // Clear input
            $(`.wall-${wallIndex}`).slideUp(); // Hide the wall
        }
    });

    // 12) Form submission
    $(".calc-form").on("submit", function (e) {
        e.preventDefault();
        const $form = $(this);

        $(".error-border").removeClass("error-border");
        $(".error-message").remove();

        const finish = $(".paint-finish").val().trim();
        const sq_meters = parseFloat($(".square-meters").val()) || 0;
        const main_wall_width = parseFloat($(".main-wall-width").val()) || 0;
        const main_wall_height = parseFloat($(".main-wall-height").val()) || 0;
        const ceiling_w = parseFloat($(".ceiling-width").val()) || 0;
        const ceiling_l = parseFloat($(".ceiling-length").val()) || 0;

        let wall_widths = [];
        let wall_heights = [];
        for (let i = 1; i <= 4; i++) {
            const wVal = parseFloat($(`.wall-${i} .wall-width`).val()) || 0;
            const hVal = parseFloat($(`.wall-${i} .wall-height`).val()) || 0;
            wall_widths.push(wVal);
            wall_heights.push(hVal);
        }

        let errors = [];

        if (!finish) {
            errors.push("Please select a finish.");
            $(".paint-finish").addClass("error-border");
        }

        if ((main_wall_width > 0 && main_wall_height <= 0) || (main_wall_height > 0 && main_wall_width <= 0)) {
            errors.push("Please fill both dimensions for the main wall.");
            $(".main-wall-width, .main-wall-height").addClass("error-border");
        }

        for (let i = 1; i <= 4; i++) {
            const w = wall_widths[i - 1];
            const h = wall_heights[i - 1];
            if ((w > 0 && h <= 0) || (h > 0 && w <= 0)) {
                errors.push(`Please fill both dimensions for wall ${i}.`);
                $(`.wall-${i} .wall-width, .wall-${i} .wall-height`).addClass("error-border");
            }
        }

        if ((ceiling_w > 0 && ceiling_l <= 0) || (ceiling_l > 0 && ceiling_w <= 0)) {
            errors.push("Please fill both dimensions for the ceiling.");
            $(".ceiling-width, .ceiling-length").addClass("error-border");
        }

        if (errors.length > 0) {
            $form.prepend(`<div class="error-message">${errors.join("<br>")}</div>`);
            return;
        }

        const formData = {
            action: "calculate_paint",
            nonce: paintCalc.nonce,
            finish: finish,
            square_meters: sq_meters,
            main_wall_width: main_wall_width,
            main_wall_height: main_wall_height,
            ceiling_width: ceiling_w,
            ceiling_length: ceiling_l,
            wall_widths: wall_widths,
            wall_heights: wall_heights
        };

        $.post(paintCalc.ajaxurl, formData, function (response) {
            if (response.success) {
                const data = response.data;
                $(".result-container").html(`
                    <h3>We think you will need...</h3>
                    <p>${data.liters} litre(s) for ${data.area} m²</p>
                    <button class="reset-btn">Recalculate</button>
                `).slideDown();
                $form.slideUp();
            } else {
                $form.prepend(`<div class="error-message">${response.data.errors.join("\n")}</div>`);
            }
        }).fail(function () {
            $form.prepend(`<div class="error-message">Error calculating paint requirements. Please try again.</div>`);
        });
    });
	
	// 7) Reset form on "Recalculate"
    $(document).on('click', '.reset-btn', function() {
        $('.calc-form')[0].reset();
        $('.result-container').slideUp();
        $('.calc-trigger').show();
        $('.accordion-content').slideUp();
        $('.accordion-icon').text('+');
        $('.error-border').removeClass('error-border');

        // Hide all dynamic walls except the first
        for (let i = 2; i <= 4; i++) {
            $(`.wall-${i}`).hide();
        }
    });
	
	
	
	
});
