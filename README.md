# Harvester

Harvester is a PHP-based CLI tool that fetches website content, including HTML, images, CSS, and JavaScript files, and saves them in a ZIP file in the Downloads folder of your device. This tool is ideal for archiving web pages and extracting assets.

## Features

- Fetches the complete HTML content of any website.
- Downloads all linked images, CSS, and JavaScript files.
- Displays a real-time progress bar for assets being downloaded.
- Provides URL validation and color-coded messages for success and errors.
- Easy-to-use command-line interface (CLI).

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/codetesla51/harvester-.git
    ```

2. Navigate to the project directory:

    ```bash
    cd harvester-
    ```
3. Run the script:

    ```bash
    php harvester.php
    ```

## Usage

1. After running the script, you will be prompted to enter a website URL.
2. The tool will begin downloading the website content (HTML, images, CSS, and JS).
3. A ZIP file containing the website's assets will be saved in your folder, named `website_<unique_id>.zip`.

## Requirements

- PHP 7.4 or higher


## Contributing

Contributions are welcome! Fork the repository, make your changes, and submit a pull request.
