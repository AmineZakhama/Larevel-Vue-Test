<template>
  <div class="categories-page">
    <div class="page-header">
      <h2>Categories</h2>
      <button @click="showCreateModal = true" class="btn btn-primary">
        + Add Category
      </button>
    </div>

    <div v-if="categoriesStore.loading" class="loading">
      Loading...
    </div>

    <div v-if="categoriesStore.error" class="error">
      {{ categoriesStore.error }}
    </div>

    <div v-if="!categoriesStore.loading" class="categories-grid">
      <div 
        v-for="category in categoriesStore.categories" 
        :key="category.id" 
        class="category-card"
      >
        <div class="category-header">
          <h3>{{ category.name }}</h3>
          <span class="fields-count-badge">
            {{ category.custom_fields?.length || 0 }} fields
          </span>
        </div>
        
        <p v-if="category.description" class="category-description">
          {{ category.description }}
        </p>

        <div v-if="category.custom_fields && category.custom_fields.length" class="fields-list">
          <div v-for="field in category.custom_fields" :key="field.id" class="field-item">
            <span class="field-name">{{ field.name }}</span>
            <span class="field-type">{{ field.field_type }}</span>
          </div>
        </div>

        <div class="category-actions">
          <button @click="editCategory(category)" class="btn btn-edit">Edit</button>
          <button @click="deleteCategory(category.id)" class="btn btn-delete">Delete</button>
        </div>
      </div>

      <div v-if="categoriesStore.categories.length === 0" class="empty-state">
        <p>No categories yet. Create one to get started!</p>
      </div>
    </div>

    <div v-if="showCreateModal || editingCategory" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h3>{{ editingCategory ? 'Edit' : 'Create' }} Category</h3>
        
        <form @submit.prevent="saveCategory">
          <div class="form-group">
            <label>Category Name *</label>
            <input 
              v-model="formData.name" 
              type="text" 
              placeholder="e.g., Personal Information" 
              required
            />
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea 
              v-model="formData.description" 
              placeholder="Brief description of this category"
              rows="3"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Assign Custom Fields</label>
            <div class="fields-selector">
              <div 
                v-for="field in customFieldsStore.customFields" 
                :key="field.id"
                class="field-checkbox"
              >
                <label>
                  <input 
                    type="checkbox" 
                    :value="field.id"
                    v-model="selectedFieldIds"
                  />
                  <span class="field-checkbox-label">
                    <strong>{{ field.name }}</strong>
                    <span class="field-type-small">{{ field.field_type }}</span>
                  </span>
                </label>
              </div>
              
              <div v-if="customFieldsStore.customFields.length === 0" class="no-fields-message">
                No custom fields available. Create some first!
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn btn-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary">
              {{ editingCategory ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useCategoriesStore } from '@/stores/categories'
import { useCustomFieldsStore } from '@/stores/customFields'

const categoriesStore = useCategoriesStore()
const customFieldsStore = useCustomFieldsStore()

const showCreateModal = ref(false)
const editingCategory = ref(null)
const formData = ref({
  name: '',
  description: ''
})
const selectedFieldIds = ref([])

onMounted(() => {
  categoriesStore.fetchCategories()
  customFieldsStore.fetchCustomFields()
})

function editCategory(category) {
  editingCategory.value = category
  formData.value = {
    name: category.name,
    description: category.description || ''
  }
  selectedFieldIds.value = category.custom_fields?.map(f => f.id) || []
}

function closeModal() {
  showCreateModal.value = false
  editingCategory.value = null
  formData.value = {
    name: '',
    description: ''
  }
  selectedFieldIds.value = []
}

async function saveCategory() {
  try {
    const data = {
      name: formData.value.name,
      description: formData.value.description,
      custom_field_ids: selectedFieldIds.value
    }

    if (editingCategory.value) {
      await categoriesStore.updateCategory(editingCategory.value.id, data)
    } else {
      await categoriesStore.createCategory(data)
    }
    closeModal()
  } catch (error) {
    alert('Error saving category: ' + error.message)
  }
}

async function deleteCategory(id) {
  if (!confirm('Are you sure you want to delete this category?')) return
  
  try {
    await categoriesStore.deleteCategory(id)
  } catch (error) {
    alert('Error deleting category: ' + error.message)
  }
}
</script>

<style scoped>
.categories-page {
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

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.category-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.75rem;
}

.category-header h3 {
  font-size: 1.25rem;
  color: #2d3748;
}

.fields-count-badge {
  background: #e2e8f0;
  color: #4a5568;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
}

.category-description {
  color: #718096;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.fields-list {
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 8px;
}

.field-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  background: white;
  border-radius: 6px;
}

.field-item:last-child {
  margin-bottom: 0;
}

.field-name {
  font-weight: 500;
  color: #2d3748;
}

.field-type {
  font-size: 0.75rem;
  color: #718096;
  background: #e2e8f0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.category-actions {
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
  max-width: 600px;
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
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
}

.fields-selector {
  max-height: 300px;
  overflow-y: auto;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  background: #f7fafc;
}

.field-checkbox {
  margin-bottom: 0.75rem;
  padding: 0.75rem;
  background: white;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.field-checkbox:hover {
  background: #edf2f7;
}

.field-checkbox label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.field-checkbox input[type="checkbox"] {
  width: auto;
  cursor: pointer;
}

.field-checkbox-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1;
}

.field-type-small {
  font-size: 0.75rem;
  color: #718096;
  background: #e2e8f0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.no-fields-message {
  text-align: center;
  padding: 2rem;
  color: #718096;
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