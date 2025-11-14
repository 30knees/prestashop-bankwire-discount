#!/bin/bash

# PrestaShop Module Packaging Script
# This script creates a proper PrestaShop 8.2 module zip package

MODULE_NAME="bankwirepaymentdiscount"
OUTPUT_FILE="${MODULE_NAME}.zip"

echo "=========================================="
echo "PrestaShop Module Package Creator"
echo "=========================================="
echo ""

# Check if logo.png exists
if [ ! -f "logo.png" ]; then
    echo "⚠️  WARNING: logo.png is missing!"
    echo "   A 140x140px PNG logo is required for PrestaShop modules."
    echo "   See LOGO_NEEDED.txt for requirements."
    echo ""
    read -p "Continue without logo? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Package creation cancelled."
        exit 1
    fi
fi

# Check if we're in a git repository
if [ -d ".git" ]; then
    echo "📦 Creating package using git archive..."
    echo "   This will automatically exclude development files."

    # Create the zip using git archive (respects .gitattributes)
    git archive --format=zip --prefix=${MODULE_NAME}/ -o ${OUTPUT_FILE} HEAD

    if [ $? -eq 0 ]; then
        echo "✅ Package created successfully: ${OUTPUT_FILE}"
        echo ""
        echo "Package contents:"
        unzip -l ${OUTPUT_FILE} | head -20
        echo ""
        echo "Package size: $(du -h ${OUTPUT_FILE} | cut -f1)"
    else
        echo "❌ Failed to create package"
        exit 1
    fi
else
    echo "📦 Creating package using zip..."
    echo "   Manually excluding development files..."

    # Create temp directory with proper structure
    TEMP_DIR=$(mktemp -d)
    MODULE_DIR="${TEMP_DIR}/${MODULE_NAME}"
    mkdir -p "${MODULE_DIR}"

    # Copy files (excluding development files)
    rsync -av --progress \
        --exclude='.git*' \
        --exclude='CONTRIBUTING.md' \
        --exclude='GITHUB_SETUP.md' \
        --exclude='LOGO_NEEDED.txt' \
        --exclude='create-module-package.sh' \
        --exclude='*.zip' \
        --exclude='.DS_Store' \
        --exclude='Thumbs.db' \
        ./ "${MODULE_DIR}/"

    # Create zip from temp directory
    cd "${TEMP_DIR}"
    zip -r "${OUTPUT_FILE}" "${MODULE_NAME}/"
    mv "${OUTPUT_FILE}" "$OLDPWD/"
    cd "$OLDPWD"

    # Cleanup
    rm -rf "${TEMP_DIR}"

    if [ -f "${OUTPUT_FILE}" ]; then
        echo "✅ Package created successfully: ${OUTPUT_FILE}"
        echo ""
        echo "Package size: $(du -h ${OUTPUT_FILE} | cut -f1)"
    else
        echo "❌ Failed to create package"
        exit 1
    fi
fi

echo ""
echo "=========================================="
echo "Installation Instructions:"
echo "=========================================="
echo "1. Go to PrestaShop Back Office"
echo "2. Navigate to Modules > Module Manager"
echo "3. Click 'Upload a module'"
echo "4. Select ${OUTPUT_FILE}"
echo "5. Install and configure the module"
echo ""
echo "✨ Done!"
