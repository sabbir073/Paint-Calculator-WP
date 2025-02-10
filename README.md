# Paint Calculator Plugin

A lightweight WordPress plugin to help you estimate how much paint is needed for walls, ceilings, and multiple additional walls. It calculates total surface area and outputs an approximate paint requirement (in liters) for two coats, including a small safety margin.

## Key Features
- Simple shortcode (`[paint_calculator]`) for embedding the calculator in any post or page.
- Supports main wall dimensions or a known square meter value.
- Dynamically adds up to 10 additional walls.
- Optional ceiling area calculation.
- Automatic rounding up and 10% extra paint safety margin.

## Installation
1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Insert the `[paint_calculator]` shortcode wherever you want the calculator to appear.

## Usage
- Select a finish (no coverage needed, only for user info).
- Enter either total square meters **or** the main wall dimensions.
- Optionally add additional walls (up to 10) and a ceiling.
- Click **Calculate** to get an estimated amount of paint required.
