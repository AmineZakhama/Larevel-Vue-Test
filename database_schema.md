# Database Schema

## Tables

### Users

Standard Laravel users table.

### Custom Fields (`custom_fields`)

Stores definitions for custom fields that can be assigned to categories.

- `id`: Primary Key
- `name`: String
- `field_type`: Enum ('text', 'textarea', 'number', 'date', 'select', 'checkbox')
- `options`: JSON (nullable) - For select/checkbox options
- `is_required`: Boolean (default: false)
- `timestamps`

### Categories (`categories`)

Groups custom fields together.

- `id`: Primary Key
- `name`: String
- `description`: Text (nullable)
- `timestamps`

### Category - Custom Field Pivot (`category_custom_field`)

Many-to-Many relationship between Categories and Custom Fields.

- `id`: Primary Key
- `category_id`: Foreign Key (`categories.id`)
- `custom_field_id`: Foreign Key (`custom_fields.id`)
- `timestamps`

### Forms (`forms`)

The main entities that contain categories.

- `id`: Primary Key
- `name`: String
- `description`: Text (nullable)
- `timestamps`

### Form - Category Pivot (`form_category`)

Many-to-Many relationship between Forms and Categories.

- `id`: Primary Key
- `form_id`: Foreign Key (`forms.id`)
- `category_id`: Foreign Key (`categories.id`)
- `timestamps`

### Form Submissions (`form_submissions`)

Stores the actual data submitted for a form.

- `id`: Primary Key
- `form_id`: Foreign Key (`forms.id`)
- `field_values`: JSON - Stores key-value pairs of custom field IDs and their values
- `display_order`: Integer (default: 0) - For ordering submissions
- `timestamps`
