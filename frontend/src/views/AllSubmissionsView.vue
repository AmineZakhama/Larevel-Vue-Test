<template>
  <div class="submissions-page">
    <div class="page-header">
      <h2>All Submissions</h2>
      <button @click="refreshData" class="btn btn-secondary">
        🔄 Refresh
      </button>
    </div>

    <div v-if="loading" class="loading">
      Loading submissions...
    </div>

    <div v-if="!loading && allSubmissions.length === 0" class="empty-state">
      <p>No submissions yet.</p>
      <router-link to="/forms" class="btn btn-primary">Create a Form</router-link>
    </div>

    <div v-if="!loading && allSubmissions.length > 0" class="table-wrapper">
      <div class="table-container">
        <table class="submissions-table">
          <thead>
            <tr>
              <th class="form-name-column">Form Name</th>
              <th v-for="field in allUniqueFields" :key="field.id" class="field-column">
                {{ field.name }}
                <span class="field-type-hint">{{ field.field_type }}</span>
              </th>
              <th class="date-column">Submitted At</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="submission in allSubmissions" :key="submission.id">
              <td class="form-name-column">
                <strong>{{ submission.formName }}</strong>
              </td>
              <td v-for="field in allUniqueFields" :key="field.id" class="field-value">
                <span v-if="submission.field_values[field.id] !== undefined && submission.field_values[field.id] !== null">
                  <span v-if="field.field_type === 'checkbox'">
                    {{ submission.field_values[field.id] ? '✓ Yes' : '✗ No' }}
                  </span>
                  <span v-else-if="field.field_type === 'date'">
                    {{ formatDate(submission.field_values[field.id]) }}
                  </span>
                  <span v-else>
                    {{ submission.field_values[field.id] }}
                  </span>
                </span>
                <span v-else class="empty-cell">-</span>
              </td>
              <td class="date-column">
                {{ formatDateTime(submission.created_at) }}
              </td>
              <td class="actions-column">
                <button @click="deleteSubmission(submission.id)" class="btn btn-delete-small">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <p class="summary-text">
          <strong>{{ allSubmissions.length }}</strong> total submission(s) across 
          <strong>{{ uniqueFormNames.length }}</strong> form(s) with 
          <strong>{{ allUniqueFields.length }}</strong> unique field(s)
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useFormsStore } from '@/stores/forms'
import { useSubmissionsStore } from '@/stores/submissions'
import { useCustomFieldsStore } from '@/stores/customFields'

const formsStore = useFormsStore()
const submissionsStore = useSubmissionsStore()
const customFieldsStore = useCustomFieldsStore()

const loading = ref(false)
const allSubmissions = ref([])
const formsMap = ref(new Map())

// Get all unique custom fields across all forms
const allUniqueFields = computed(() => {
  const fieldsMap = new Map()
  
  // Collect all fields from all forms
  formsStore.forms.forEach(form => {
    if (form.categories) {
      form.categories.forEach(category => {
        if (category.custom_fields) {
          category.custom_fields.forEach(field => {
            if (!fieldsMap.has(field.id)) {
              fieldsMap.set(field.id, field)
            }
          })
        }
      })
    }
  })
  
  return Array.from(fieldsMap.values())
})

const uniqueFormNames = computed(() => {
  const names = new Set(allSubmissions.value.map(s => s.formName))
  return Array.from(names)
})

onMounted(async () => {
  await loadAllData()
})

async function loadAllData() {
  loading.value = true
  try {
    // Load all forms with their categories and fields
    await formsStore.fetchForms()
    await customFieldsStore.fetchCustomFields()
    
    // Create a map of form IDs to form names
    formsStore.forms.forEach(form => {
      formsMap.value.set(form.id, form.name)
    })
    
    // Load submissions for all forms
    const submissionPromises = formsStore.forms.map(async (form) => {
      try {
        await submissionsStore.fetchSubmissions(form.id)
        return submissionsStore.submissions.map(sub => ({
          ...sub,
          formName: form.name
        }))
      } catch (error) {
        console.error(`Error loading submissions for form ${form.id}:`, error)
        return []
      }
    })
    
    const allSubmissionsArrays = await Promise.all(submissionPromises)
    allSubmissions.value = allSubmissionsArrays.flat().sort((a, b) => 
      new Date(b.created_at) - new Date(a.created_at)
    )
    
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
}

async function refreshData() {
  await loadAllData()
}

async function deleteSubmission(id) {
  if (!confirm('Are you sure you want to delete this submission?')) return
  
  try {
    await submissionsStore.deleteSubmission(id)
    await loadAllData()
  } catch (error) {
    alert('Error deleting submission: ' + error.message)
  }
}

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

function formatDateTime(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString()
}
</script>

<style scoped>
.submissions-page {
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
  margin: 0;
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

.loading {
  text-align: center;
  padding: 3rem;
  font-size: 1.125rem;
  color: #4a5568;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-state p {
  font-size: 1.125rem;
  color: #718096;
  margin-bottom: 1.5rem;
}

.table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  overflow: hidden;
}

.table-container {
  overflow-x: auto;
}

.submissions-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.submissions-table thead {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  position: sticky;
  top: 0;
  z-index: 10;
}

.submissions-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  white-space: nowrap;
}

.field-type-hint {
  display: block;
  font-size: 0.7rem;
  font-weight: 400;
  opacity: 0.85;
  margin-top: 0.25rem;
}

.submissions-table tbody tr {
  border-bottom: 1px solid #e2e8f0;
  transition: background 0.2s ease;
}

.submissions-table tbody tr:hover {
  background: #f7fafc;
}

.submissions-table td {
  padding: 1rem;
  color: #4a5568;
}

.form-name-column {
  min-width: 180px;
  font-weight: 600;
  color: #2d3748;
  background: #f7fafc;
  position: sticky;
  left: 0;
  z-index: 5;
}

.submissions-table tbody tr:hover .form-name-column {
  background: #edf2f7;
}

.field-column {
  min-width: 150px;
}

.field-value {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.empty-cell {
  color: #cbd5e0;
  font-style: italic;
}

.date-column {
  min-width: 160px;
  white-space: nowrap;
  font-size: 0.85rem;
  color: #718096;
}

.actions-column {
  width: 120px;
  text-align: center;
}

.btn-delete-small {
  background: #fc8181;
  color: white;
  padding: 0.4rem 0.8rem;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-delete-small:hover {
  background: #f56565;
}

.table-footer {
  padding: 1.5rem;
  background: #f7fafc;
  border-top: 2px solid #e2e8f0;
  text-align: center;
}

.summary-text {
  font-size: 1rem;
  color: #4a5568;
  margin: 0;
}

.summary-text strong {
  color: #667eea;
}
</style>
