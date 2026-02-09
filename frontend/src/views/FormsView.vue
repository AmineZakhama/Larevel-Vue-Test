```
<template>
  <div class="forms-page">
    <div class="page-header">
      <h2>Forms</h2>
      <div class="header-actions">
        <router-link to="/submissions" class="btn btn-secondary">
          View All Submissions
        </router-link>
        <button @click="showCreateModal = true" class="btn btn-primary">
          + Create Form
        </button>
      </div>
    </div>

    <div v-if="formsStore.loading" class="loading">Loading...</div>

    <div v-if="formsStore.error" class="error">
      {{ formsStore.error }}
    </div>

    <div v-if="!formsStore.loading" class="forms-grid">
      <div v-for="form in formsStore.forms" :key="form.id" class="form-card">
        <div class="form-header">
          <h3>{{ form.name }}</h3>
          <span class="categories-count-badge">
            {{ form.categories?.length || 0 }} categories
          </span>
        </div>

        <p v-if="form.description" class="form-description">
          {{ form.description }}
        </p>

        <div
          v-if="form.categories && form.categories.length"
          class="categories-list"
        >
          <div
            v-for="category in form.categories"
            :key="category.id"
            class="category-item"
          >
            <div class="category-item-header">
              <strong>{{ category.name }}</strong>
              <span class="fields-badge"
                >{{ category.custom_fields?.length || 0 }} fields</span
              >
            </div>
            <div
              v-if="category.custom_fields && category.custom_fields.length"
              class="fields-preview"
            >
              <span
                v-for="field in category.custom_fields.slice(0, 3)"
                :key="field.id"
                class="field-tag"
              >
                {{ field.name }}
              </span>
              <span
                v-if="category.custom_fields.length > 3"
                class="more-fields"
              >
                +{{ category.custom_fields.length - 3 }} more
              </span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button @click="editForm(form)" class="btn btn-edit">Edit</button>
          <button @click="deleteForm(form.id)" class="btn btn-delete">
            Delete
          </button>
        </div>
      </div>

      <div v-if="formsStore.forms.length === 0" class="empty-state">
        <p>No forms yet. Create one to get started!</p>
      </div>
    </div>

    <div
      v-if="showCreateModal || editingForm"
      class="modal-overlay"
      @click.self="closeModal"
    >
      <div class="modal">
        <h3>{{ editingForm ? "Edit" : "Create" }} Form</h3>

        <form @submit.prevent="saveForm">
          <div class="form-group">
            <label>Form Name *</label>
            <input
              v-model="formData.name"
              type="text"
              placeholder="e.g., Customer Registration"
              required
            />
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea
              v-model="formData.description"
              placeholder="Brief description of this form"
              rows="3"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Select Categories</label>
            <p class="availability-hint">
              Only showing categories not used in other forms
            </p>
            <div class="categories-selector">
              <div
                v-for="category in availableCategories"
                :key="category.id"
                class="category-checkbox"
              >
                <label>
                  <input
                    type="checkbox"
                    :value="category.id"
                    v-model="selectedCategoryIds"
                  />
                  <span class="category-checkbox-label">
                    <div>
                      <strong>{{ category.name }}</strong>
                      <p
                        v-if="category.description"
                        class="category-desc-small"
                      >
                        {{ category.description }}
                      </p>
                    </div>
                    <span class="fields-count-small">
                      {{ category.custom_fields?.length || 0 }} fields
                    </span>
                  </span>
                </label>
              </div>

              <div
                v-if="availableCategories.length === 0"
                class="no-categories-message"
              >
                <template v-if="categoriesStore.categories.length === 0">
                  No categories created yet. Create some first!
                </template>
                <template v-else>
                  All categories are already used in other forms.
                </template>
              </div>
            </div>
          </div>

          <!-- Dynamic Fields Section - Only show when creating new form with categories selected -->
          <div
            v-if="!editingForm && selectedFields.length > 0"
            class="form-fields-section"
          >
            <h4>Fill in the form fields</h4>
            <p class="fields-hint">
              Fill in the initial data for this form submission
            </p>

            <div
              v-for="category in selectedCategories"
              :key="category.id"
              class="fields-category-section"
            >
              <h5 class="category-title">{{ category.name }}</h5>

              <div
                v-for="field in category.custom_fields"
                :key="field.id"
                class="dynamic-field-group"
              >
                <label>
                  {{ field.name }}
                  <span v-if="field.is_required" class="required-star">*</span>
                </label>

                <input
                  v-if="field.field_type === 'text'"
                  v-model="fieldValues[field.id]"
                  type="text"
                  :required="field.is_required"
                  :placeholder="`Enter ${field.name.toLowerCase()}`"
                />

                <textarea
                  v-else-if="field.field_type === 'textarea'"
                  v-model="fieldValues[field.id]"
                  :required="field.is_required"
                  :placeholder="`Enter ${field.name.toLowerCase()}`"
                  rows="3"
                ></textarea>

                <input
                  v-else-if="field.field_type === 'number'"
                  v-model="fieldValues[field.id]"
                  type="number"
                  :required="field.is_required"
                  :placeholder="`Enter ${field.name.toLowerCase()}`"
                />

                <input
                  v-else-if="field.field_type === 'date'"
                  v-model="fieldValues[field.id]"
                  type="date"
                  :required="field.is_required"
                />

                <select
                  v-else-if="field.field_type === 'select'"
                  v-model="fieldValues[field.id]"
                  :required="field.is_required"
                >
                  <option value="">Select an option...</option>
                  <option
                    v-for="option in field.options"
                    :key="option"
                    :value="option"
                  >
                    {{ option }}
                  </option>
                </select>

                <label
                  v-else-if="field.field_type === 'checkbox'"
                  class="checkbox-label"
                >
                  <input type="checkbox" v-model="fieldValues[field.id]" />
                  <span>Check this box</span>
                </label>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn btn-cancel">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary">
              {{ editingForm ? "Update" : "Create & Submit" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { useFormsStore } from "@/stores/forms";
import { useCategoriesStore } from "@/stores/categories";
import { useSubmissionsStore } from "@/stores/submissions";
import api from "@/services/api";

const router = useRouter();
const formsStore = useFormsStore();
const categoriesStore = useCategoriesStore();
const submissionsStore = useSubmissionsStore();

const showCreateModal = ref(false);
const editingForm = ref(null);
const formData = ref({
  name: "",
  description: "",
});
const selectedCategoryIds = ref([]);
const fieldValues = ref({});
const availableCategories = ref([]);

const selectedCategories = computed(() => {
  return availableCategories.value.filter((cat) =>
    selectedCategoryIds.value.includes(cat.id),
  );
});

const selectedFields = computed(() => {
  const fields = [];
  selectedCategories.value.forEach((category) => {
    if (category.custom_fields) {
      fields.push(...category.custom_fields);
    }
  });
  return fields;
});


watch(showCreateModal, async (isOpen) => {
  if (isOpen && !editingForm.value) {
    await fetchAvailableCategories();
  }
});

watch(editingForm, async (form) => {
  if (form) {
    await fetchAvailableCategories(form.id);
  }
});

onMounted(() => {
  formsStore.fetchForms();
  categoriesStore.fetchCategories();
});

async function fetchAvailableCategories(excludeFormId = null) {
  try {
    const response = await api.getAvailableCategories(excludeFormId);

    // Check both standard Laravel Resource (data.data) and direct array (data)
    let data = [];
    if (response.data && Array.isArray(response.data.data)) {
      data = response.data.data;
    } else if (Array.isArray(response.data)) {
      data = response.data;
    } else {
      console.warn("Unexpected API response format:", response.data);
    }

    availableCategories.value = data;
  } catch (error) {
    console.error("Error fetching available categories:", error);
    availableCategories.value = [];
  }
}

function editForm(form) {
  editingForm.value = form;
  formData.value = {
    name: form.name,
    description: form.description || "",
  };
  selectedCategoryIds.value = form.categories?.map((c) => c.id) || [];
}

function closeModal() {
  showCreateModal.value = false;
  editingForm.value = null;
  formData.value = {
    name: "",
    description: "",
  };
  selectedCategoryIds.value = [];
  fieldValues.value = {};
  availableCategories.value = [];
}

async function saveForm() {
  try {
    const data = {
      name: formData.value.name,
      description: formData.value.description,
      category_ids: selectedCategoryIds.value,
    };

    if (editingForm.value) {
      await formsStore.updateForm(editingForm.value.id, data);
    } else {
      const newForm = await formsStore.createForm(data);
      if (Object.keys(fieldValues.value).length > 0) {
        // Ensure field_values is an object with ID keys
        const submissionValues = {};
        for (const [key, value] of Object.entries(fieldValues.value)) {
          submissionValues[String(key)] = value;
        }

        const submissionData = {
          field_values: submissionValues,
        };

        try {
          const result = await submissionsStore.createSubmission(
            newForm.id,
            submissionData,
          );
        } catch (error) {
          console.error("Error creating submission:", error);
          alert("Form created but submission failed: " + error.message);
        }
      }
    }
    closeModal();
  } catch (error) {
    alert("Error saving form: " + error.message);
  }
}

async function deleteForm(id) {
  if (!confirm("Are you sure you want to delete this form?")) return;

  try {
    await formsStore.deleteForm(id);
  } catch (error) {
    alert("Error deleting form: " + error.message);
  }
}
</script>

<style scoped>
.forms-page {
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

.header-actions {
  display: flex;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
  text-decoration: none;
  display: inline-block;
}

.btn-secondary {
  background: #4a5568;
  color: white;
}

.btn-secondary:hover {
  background: #2d3748;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.forms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

.form-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
}

.form-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.75rem;
}

.form-header h3 {
  font-size: 1.25rem;
  color: #2d3748;
}

.categories-count-badge {
  background: #e2e8f0;
  color: #4a5568;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
}

.form-description {
  color: #718096;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.categories-list {
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 8px;
}

.category-item {
  padding: 0.75rem;
  margin-bottom: 0.75rem;
  background: white;
  border-radius: 6px;
}

.category-item:last-child {
  margin-bottom: 0;
}

.category-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.fields-badge {
  font-size: 0.75rem;
  color: #718096;
  background: #e2e8f0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.fields-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.field-tag {
  font-size: 0.75rem;
  background: #bee3f8;
  color: #2c5282;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.more-fields {
  font-size: 0.75rem;
  color: #718096;
  font-style: italic;
}

.form-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.btn-edit {
  background: #4299e1;
  color: white;
  padding: 0.5rem;
}

.btn-edit:hover {
  background: #3182ce;
}

.btn-delete {
  background: #fc8181;
  color: white;
  padding: 0.5rem;
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

.loading,
.error {
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
  max-width: 700px;
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

.categories-selector {
  max-height: 400px;
  overflow-y: auto;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  background: #f7fafc;
}

.category-checkbox {
  margin-bottom: 0.75rem;
  padding: 1rem;
  background: white;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.category-checkbox:hover {
  background: #edf2f7;
}

.category-checkbox label {
  display: flex;
  align-items: start;
  gap: 0.75rem;
  cursor: pointer;
}

.category-checkbox input[type="checkbox"] {
  margin-top: 0.25rem;
  width: auto;
  cursor: pointer;
}

.category-checkbox-label {
  display: flex;
  justify-content: space-between;
  align-items: start;
  flex: 1;
  gap: 1rem;
}

.category-desc-small {
  font-size: 0.75rem;
  color: #718096;
  margin-top: 0.25rem;
  font-weight: normal;
}

.fields-count-small {
  font-size: 0.75rem;
  color: #718096;
  background: #e2e8f0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  white-space: nowrap;
}

.fields-preview-small {
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: #f7fafc;
  border-radius: 4px;
  color: #4a5568;
}

.no-categories-message {
  text-align: center;
  padding: 2rem;
  color: #718096;
}

.availability-hint {
  font-size: 0.85rem;
  color: #718096;
  margin: 0.5rem 0 1rem 0;
  font-style: italic;
}

/* Dynamic Form Fields Section */
.form-fields-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #e2e8f0;
}

.form-fields-section h4 {
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.fields-hint {
  color: #718096;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.fields-category-section {
  margin-bottom: 2rem;
}

.category-title {
  color: #4a5568;
  font-size: 1rem;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: #f7fafc;
  border-left: 4px solid #667eea;
  border-radius: 4px;
}

.dynamic-field-group {
  margin-bottom: 1.25rem;
}

.dynamic-field-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #4a5568;
  font-size: 0.9rem;
}

.required-star {
  color: #e53e3e;
  margin-left: 0.25rem;
}

.dynamic-field-group input,
.dynamic-field-group textarea,
.dynamic-field-group select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.dynamic-field-group input:focus,
.dynamic-field-group textarea:focus,
.dynamic-field-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: normal !important;
}

.checkbox-label input[type="checkbox"] {
  width: auto;
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
