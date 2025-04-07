# EWM OpenAI Bundle | Sulu CMS Integration
[![By EWM](https://img.shields.io/badge/By-EWM-blue.svg)](https://ewm.com)
[![License](https://img.shields.io/packagist/l/ewm/openai-bundle.svg)](https://packagist.org/packages/ewm/openai-bundle)
[![Version](https://img.shields.io/packagist/v/ewm/openai-bundle.svg)](https://packagist.org/packages/ewm/openai-bundle)
A Symfony/Sulu bundle that **generates AI-powered descriptions for media** using OpenAI.  
Perfect for automating metadata in Sulu CMS!

---

## ✨ Features
- **Auto-generate descriptions** for Sulu media uploads using OpenAI GPT.
- **Customizable prompts** tailored to media types (e.g., "Describe this image for accessibility").
- **Admin UI integration** – Generate descriptions with a click in Sulu Admin.
- **Event-driven** – Automatically trigger descriptions on media upload.

---

## 📦 Installation

1. Install the bundle via Composer:
   ```bash
   composer require ewm/openai-bundle

2. Add the necessary environment variables to your `.env` file:

   Before using the bundle, ensure that the following environment variables are added to your `.env` file:

   - **`HOSTNAME`**: The public URL or hostname of your website. This variable defines the base URL from which the bundle can access resources, such as images.

     Example:
     ```dotenv
     HOSTNAME=https://example.com
     ```

   - **`OPEN_API_KEY`**: The API key required to authenticate your requests to the OpenAI service. You can generate and obtain this key directly from your OpenAI account.

     Example:
     ```dotenv
     OPEN_API_KEY=your-api-key-here
     ```

   **Example `.env` file:**
   ```dotenv
   # OpenAI API Configuration
   HOSTNAME=https://example.com
   OPEN_API_KEY=your-api-key-here


