# 🏢 Logo Setup Guide for Travel Order PDF Reports

## 📁 **Where to Put Your Logo Files**

### **Primary Logo Location:**
```
public/images/logo/dict-logo.png
```

### **Backup Logo Location:**
```
public/images/logo/logo.png
```

## 📋 **Logo Requirements**

### **Recommended Specifications:**
- **Format**: PNG (preferred) or JPG
- **Size**: 300x300 pixels minimum
- **Resolution**: 300 DPI for crisp printing
- **Background**: Transparent (PNG) or white background
- **File Size**: Under 500KB for fast loading

### **Naming Convention:**
- **Primary**: `dict-logo.png` (DICT specific logo)
- **Backup**: `logo.png` (generic logo)
- **Alternative**: `dict-logo.jpg` or `logo.jpg`

## 🛠️ **How to Add Your Logo**

### **Step 1: Prepare Your Logo File**
1. Save your logo as `dict-logo.png`
2. Resize to approximately 300x300 pixels
3. Ensure transparent or white background

### **Step 2: Copy to Correct Location**
Copy your logo file to:
```
C:\xampp\htdocs\projects\laravel\travel-order-system\public\images\logo\dict-logo.png
```

### **Step 3: Test the Logo**
1. Generate a travel order PDF
2. Check if logo appears in the header
3. If not visible, check file name and location

## 🔧 **Troubleshooting**

### **Logo Not Appearing?**
1. **Check file path**: Ensure file is in `public/images/logo/`
2. **Check file name**: Must be exactly `dict-logo.png` or `logo.png`
3. **Check file permissions**: Ensure readable by web server
4. **Check file format**: Use PNG or JPG format

### **Logo Too Large/Small?**
The PDF template automatically resizes to 60x60 pixels. For best quality:
- Use high-resolution source image
- Let the template handle resizing
- Maintain square aspect ratio

### **Logo Quality Issues?**
- Use PNG format for best quality
- Ensure source image is high resolution
- Avoid heavily compressed images

## 📂 **File Structure After Setup**
```
public/
├── images/
│   ├── logo/
│   │   ├── dict-logo.png    ← Your main logo here
│   │   └── logo.png         ← Backup logo (optional)
│   └── ...
├── css/
├── js/
└── ...
```

## 🎯 **Logo Usage in PDF**

The system will automatically:
1. **First**: Look for `dict-logo.png`
2. **Then**: Look for `logo.png`  
3. **Finally**: Show placeholder if no logo found

## 📝 **Notes**

- Logo appears in the top-left of every PDF
- Logo is automatically sized to 60x60 pixels
- System supports PNG and JPG formats
- Transparent backgrounds work best
- Logo loads from filesystem, not database

---
**Quick Setup**: Just copy your logo as `dict-logo.png` to `public/images/logo/` folder!
