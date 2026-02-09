<template>
  <div class="custom-fields-page">
    <div class="page-header">
      <h2>Custom Fields</h2>
      <button @click="showCreateModal = true" class="btn btn-primary">
        + Add Custom Field
      </button>
    </div>

    <div v-if="customFieldsStore.loading" class="loading">
      Loading...
    </div>

    <div v-if="customFieldsStore.error" class="error">
      {{ customFieldsStore.error }}
    </div>

    <div v-if="!customFieldsStore.loading" class="fields-grid">
      <div 
        v-for="field in customFieldsStore.customFields" 
        :key="field.id" 
        class="field-card"
      >
        <div class="field-header">
          <h3>{{ field.name }}</h3>
          <span class="field-type-badge">{{ field.field_type }}</span>
        </div>
        
        <div class="field-meta">
          <span v-if="field.is_required" class="required-badge">Required</span>
          <span v-if="field.options && field.options.length" class="options-count">
            {{ field.options.length }} options
          </span>
        </div>

        <div class="field-actions">
          <button @click="editField(field)" class="btn btn-edit">Edit</button>
          <button @click="deleteField(field.id)" class="btn btn-delete">Delete</button>
        </div>
      </div>

      <div v-if="customFieldsStore.customFields.length === 0" class="empty-state">
        <p>No custom fields yet. Create one to get started!</p>
      </div>
    </div>

    <div v-if="showCreateModal || editingField" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h3>{{ editingField ? 'Edit' : 'Create' }} Custom Field</h3>
        
        <form @submit.prevent="saveField">
          <div class="form-group">
            <label>Field Name *</label>
            <input 
              v-model="formData.name" 
              type="text" 
              placeholder="e.g., Email Address" 
              required
            />
          </div>

          <div class="form-group">
            <label>Field Type *</label>
            <select v-model="formData.field_type" required>
              <option value="">Select type...</option>
              <option value="text">Text</option>
              <option value="textarea">Textarea</option>
              <option value="number">Number</option>
              <option value="date">Date</option>
              <option value="select">Select/Dropdown</option>
              <option value="checkbox">Checkbox</option>
            </select>
          </div>

          <div v-if="formData.field_type === 'select'" class="form-group">
            <label>Options (one per line)</label>
            <textarea 
              v-model="optionsText" 
              placeholder="Option 1&#10;Option 2&#10;Option 3"
              rows="4"
            ></textarea>
          </div>

          <div class="form-group checkbox-group">
            <label>
              <input type="checkbox" v-model="formData.is_required" />
              Required field
            </label>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn btn-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary">
              {{ editingField ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useCustomFieldsStore } from '@/stores/customFields'

const customFieldsStore = useCustomFieldsStore()

const showCreateModal = ref(false)
const editingField = ref(null)
const formData = ref({
  name: '',
  field_type: '',
  options: [],
  is_required: false
})

const optionsText = computed({
  get() {
    return formData.value.options?.join('\n') || ''
  },
  set(value) {
    formData.value.options = value.split('\n').filter(opt => opt.trim())
  }
})

onMounted(() => {
  customFieldsStore.fetchCustomFields()
})

function editField(field) {
  editingField.value = field
  formData.value = {
    name: field.name,
    field_type: field.field_type,
    options: field.options || [],
    is_required: field.is_required
  }
}

function closeModal() {
  showCreateModal.value = false
  editingField.value = null
  formData.value = {
    name: '',
    field_type: '',
    options: [],
    is_required: false
  }
}

async function saveField() {
  try {
    if (editingField.value) {
      await customFieldsStore.updateCustomField(editingField.value.id, formData.value)
    } else {
      await customFieldsStore.createCustomField(formData.value)
    }
    closeModal()
  } catch (error) {
    alert('Error saving field: ' + error.message)
  }
}

async function deleteField(id) {
  if (!confirm('Are you sure you want to delete this field?')) return
  
  try {
    await customFieldsStore.deleteCustomField(id)
  } catch (error) {
    alert('Error deleting field: ' + error.message)
  }
}
</script>

<style scoped>
.custom-fields-page {
  padding: 2rem 0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h2 {
  font-size: 2rem;
  color: #2d3748;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.fields-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.field-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.field-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.field-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1rem;
}

.field-header h3 {
  font-size: 1.25rem;
  color: #2d3748;
}

.field-type-badge {
  background: #e2e8f0;
  color: #4a5568;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
}

.field-meta {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.required-badge {
  background: #fed7d7;
  color: #c53030;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.options-count {
  background: #bee3f8;
  color: #2c5282;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.field-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit {
  flex: 1;
  background: #4299e1;
  color: white;
  padding: 0.5rem;
  font-size: 0.875rem;
}

.btn-edit:hover {
  background: #3182ce;
}

.btn-delete {
  flex: 1;
  background: #fc8181;
  color: white;
  padding: 0.5rem;
  font-size: 0.875rem;
}

.btn-delete:hover {
  background: #f56565;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem;
  color: #718096;
  font-size: 1.125rem;
}

.loading, .error {
  text-align: center;
  padding: 2rem;
  font-size: 1.125rem;
}

.error {
  color: #e53e3e;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal h3 {
  margin-bottom: 1.5rem;
  color: #2d3748;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #4a5568;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
}

.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
  width: auto;
  cursor: pointer;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.modal-actions .btn {
  flex: 1;
}

.btn-cancel {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-cancel:hover {
  background: #cbd5e0;
}
</style>