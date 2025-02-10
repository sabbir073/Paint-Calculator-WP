<?php

/*
 * Plugin Name:       Paint Calculator
 * Plugin URI:        https://github.com/sabbir073/Paint-Calculator-WP
 * Description:       Advanced paint calculator with dynamic walls (up to 10), validation, and error handling.
 * Version:           1.0.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Md Sabbir Ahmed
 * Author URI:        https://github.com/sabbir073
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/sabbir073/Paint-Calculator-WP
 * Text Domain:       paint-calculator
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

class Paint_Calculator {
    public function __construct() {
        add_shortcode('paint_calculator', [$this, 'render_calculator']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // AJAX hooks
        add_action('wp_ajax_calculate_paint', [$this, 'calculate_paint']);
        add_action('wp_ajax_nopriv_calculate_paint', [$this, 'calculate_paint']);
    }

    public function enqueue_assets() {
        // CSS
        wp_enqueue_style(
            'paint-calculator-css',
            plugins_url('assets/css/paint-calculator.css', __FILE__),
            [],
            '2.0'
        );

        // JS
        wp_enqueue_script(
            'paint-calculator-js',
            plugins_url('assets/js/paint-calculator.js', __FILE__),
            ['jquery'],
            '2.0',
            true
        );

        // Localize AJAX data
        wp_localize_script('paint-calculator-js', 'paintCalc', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('paint_calc_nonce')
        ]);
    }

    public function render_calculator() {
        ob_start(); ?>
        <div class="paint-calculator">
            <!-- Trigger link -->
            <a href="#" class="calc-trigger">How much paint do I need? ▼</a>

            <!-- The Form -->
            <form class="calc-form" style="display: none;">
                <div class="form-header">
                    <h2>Paint Calculator</h2>
                    <span class="close-form">&times;</span>
                </div>

                <!-- 1. Finish Selection -->
                <div class="form-section">
                    <span class="label">1. Which finish are you using?</span>
                    <select class="paint-finish" required>
                        <option value="">Select a Finish</option>
                        <!-- Option values not used for coverage, just ensure user picks something -->
                        <option>Intelligent ASP (All Surface Primer)</option>
                        <option>Absolute Matt Emulsion</option>
                        <option>Intelligent Matt Emulsion</option>
                        <option>Intelligent Eggshell</option>
                        <option>Intelligent Gloss</option>
                        <option>Intelligent Satin</option>
                        <option>Intelligent Floor Paint</option>
                        <option>Traditional Oil Gloss</option>
                        <option>Intelligent Masonry Paint</option>
                        <option>Distemper</option>
                        <option>Limewash</option>
                        <option>Intelligent Exterior Eggshell</option>
                        <option>Interior Oil Eggshell</option>
                        <option>Tom's Oil Eggshell</option>
                    </select>
                </div>

                <!-- 2. Square Metres (for main area if no main wall) -->
                <div class="form-section">
                    <span class="label">2. Do you know the square metres to be painted?</span>
                    <div class="input-group">
                        <input type="number" step="1" class="square-meters" placeholder="0" disabled>
                        <span class="unit">m²</span>
                    </div>
                </div>

                <!-- 3. Main Wall -->
                <div class="form-section">
                    <span class="label">3. Enter the dimensions of the main wall</span>
                    <div class="dimension-fields">
                        <div class="input-group">
                            <input type="number" step="1" class="main-wall-width" placeholder="Width" disabled>
                            <span class="unit">m</span>
                        </div>
                        <div class="input-group">
                            <input type="number" step="1" class="main-wall-height" placeholder="Height" disabled>
                            <span class="unit">m</span>
                        </div>
                    </div>
                </div>

                

				
				
				
				<!-- 5. Ceiling -->
                <div class="form-accordion">
                    <div class="accordion-header wall-empty">
                       <span class="label"> 4. Do you need to add dimensions for a another wall? (up to 4) </span>
                        <span class="accordion-icon ">+</span>
                    </div>
                    <div class="accordion-content" style="display: none;">
                        <div class="dynamic-walls-wrapper">
                        <!-- We'll create 10 .additional-wall pairs. 
                             The first is shown, the rest are hidden by default. 
                             If the user fully fills a pair, the next one is revealed by JS. -->

                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="dimension-fields additional-wall wall-<?php echo $i; ?>" 
                             style="<?php echo ($i === 1) ? '' : 'display: none;'; ?>">
                            <label>Wall <?php echo $i; ?></label>
                            <div class="input-group">
                                <input type="number" step="1" 
                                       class="wall-width" 
                                       placeholder="Width" 
                                       data-wall-index="<?php echo $i; ?>"
                                       disabled>
                                <span class="unit">m</span>
                            </div>
                            <div class="input-group">
                                <input type="number" step="1" 
                                       class="wall-height" 
                                       placeholder="Height" 
                                       data-wall-index="<?php echo $i; ?>"
                                       disabled>
                                <span class="unit">m</span>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    </div>
                </div>
				
				
				
				
				
				
              
				
				<!-- 5. Ceiling -->
                <div class="form-accordion">
                    <div class="accordion-header">
                       <span class="label"> 5. Are you painting a ceiling? </span>
                        <span class="accordion-icon">+</span>
                    </div>
                    <div class="accordion-content" style="display: none;">
                        <div class="dimension-fields">
                            <div class="input-group">
                                <input type="number" step="1" class="ceiling-width" placeholder="Width" disabled>
                                <span class="unit">m</span>
                            </div>
                            <div class="input-group">
                                <input type="number" step="1" class="ceiling-length" placeholder="Length" disabled>
                                <span class="unit">m</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculate -->
                <button type="submit" class="calculate-btn" disabled>Calculate</button>
                
                <div class="disclaimer">
                    Disclaimer: This calculator is provided for general information
                    and illustration purposes only. Results are to be used only as
                    estimates and are not intended as definitive advice.
                </div>
            </form>

            <!-- Result Container -->
            <div class="result-container" style="display: none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function calculate_paint() {
        // Security check
        check_ajax_referer('paint_calc_nonce', 'nonce');

        $errors = [];

        // Basic fields
        $finish            = sanitize_text_field($_POST['finish'] ?? '');
        $sq_m_input        = isset($_POST['square_meters']) ? floatval($_POST['square_meters']) : 0;
        $main_w            = isset($_POST['main_wall_width']) ? floatval($_POST['main_wall_width']) : 0;
        $main_h            = isset($_POST['main_wall_height']) ? floatval($_POST['main_wall_height']) : 0;
        $ceiling_w         = isset($_POST['ceiling_width']) ? floatval($_POST['ceiling_width']) : 0;
        $ceiling_l         = isset($_POST['ceiling_length']) ? floatval($_POST['ceiling_length']) : 0;

        // Gather up to 10 wall dimensions from arrays
        $wall_widths  = $_POST['wall_widths']  ?? []; // array of floats or strings
        $wall_heights = $_POST['wall_heights'] ?? []; // array of floats or strings

        // 1) Check finish
        if (empty($finish)) {
            $errors[] = 'Please select a finish.';
        }

        // 2) Check partial fill for main wall
        if (($main_w > 0 && $main_h <= 0) || ($main_h > 0 && $main_w <= 0)) {
            $errors[] = 'Please fill both dimensions for the main wall.';
        }

        // 3) We'll parse each additional wall for partial fill
        //    If both width & height > 0 => add area
        //    If one is > 0 and the other is 0 => partial fill error
        //    If both are 0 => skip
        $additional_walls = []; // store valid pairs for final area calculation

        for ($i = 0; $i < 4; $i++) {
            $w = isset($wall_widths[$i]) ? floatval($wall_widths[$i]) : 0;
            $h = isset($wall_heights[$i]) ? floatval($wall_heights[$i]) : 0;

            // partial fill check
            if (($w > 0 && $h <= 0) || ($h > 0 && $w <= 0)) {
                $errors[] = 'Please fill both dimensions for wall '.($i+1).'.';
            } elseif ($w > 0 && $h > 0) {
                // fully filled => push to array
                $additional_walls[] = ['width'=>$w, 'height'=>$h];
            }
        }

        // 4) Ceiling partial check
        if (($ceiling_w > 0 && $ceiling_l <= 0) || ($ceiling_l > 0 && $ceiling_w <= 0)) {
            $errors[] = 'Please fill both dimensions for the ceiling.';
        }

        // If errors so far, stop
        if (!empty($errors)) {
            wp_send_json_error(['errors' => $errors]);
        }

        // 5) Calculate total area
        //    - If main wall is filled => ignore sq_m_input
        //    - else use sq_m_input
        //    - add any additional walls
        //    - add ceiling if fully filled
        $main_area = 0;
        if ($main_w > 0 && $main_h > 0) {
            $main_area = $main_w * $main_h;
        } else {
            $main_area = $sq_m_input;
        }

        $walls_area = 0;
        foreach ($additional_walls as $wdata) {
            $walls_area += ($wdata['width'] * $wdata['height']);
        }

        $ceiling_area = 0;
        if ($ceiling_w > 0 && $ceiling_l > 0) {
            $ceiling_area = $ceiling_w * $ceiling_l;
        }

        $total_area = $main_area + $walls_area + $ceiling_area;
        if ($total_area <= 0) {
            wp_send_json_error(['errors' => ['Please provide at least one valid area or dimension.']]);
        }

        // 6) Paint calculation
        // coverage=14, coats=2, margin=1.1, then round up (ceil)
        $coverage      = 14;
        $coats         = 2;
        $margin        = 1.1;
        
        $paint_float   = ($total_area * $coats * $margin) / $coverage;
        $paint_needed  = ceil($paint_float);

        // 7) Return success
        wp_send_json_success([
            'liters' => $paint_needed,
            'area'   => round($total_area, 2)
        ]);
    }
}

new Paint_Calculator();
