<template>
  <div class="submissions-page">
    <div class="page-header">
      <div class="header-left">
        <button @click="goBack" class="btn btn-back">← Back to Forms</button>
        <h2>Submissions: {{ formName }}</h2>
      </div>
      <div class="header-actions">
        <button @click="refreshSubmissions" class="btn btn-secondary">
          🔄 Refresh
        </button>
      </div>
    </div>

    <div v-if="submissionsStore.loading" class="loading">
      Loading submissions...
    </div>

    <div v-if="submissionsStore.error" class="error">
      {{ submissionsStore.error }}
    </div>

    <div v-if="!submissionsStore.loading && submissions.length === 0" class="empty-state">
      <p>No submissions yet for this form.</p>
      <button @click="fillForm" class="btn btn-primary">+ Add First Submission</button>
    </div>

    <div v-if="!submissionsStore.loading && submissions.length > 0" class="table-container">
      <table class="submissions-table">
        <thead>
          <tr>
            <th class="id-column">#</th>
            <th v-for="field in allFields" :key="field.id">
              {{ field.name }}
              <span class="field-type-hint">{{ field.field_type }}</span>
            </th>
            <th class="date-column">Submitted At</th>
            <th class="actions-column">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(submission, index) in submissions" :key="submission.id">
            <td class="id-column">{{ index + 1 }}</td>
            <td v-for="field in allFields" :key="field.id" class="field-value">
              <span v-if="field.field_type === 'checkbox'">
                {{ submission.field_values[field.id] ? '✓ Yes' : '✗ No' }}
              </span>
              <span v-else-if="field.field_type === 'date'">
                {{ formatDate(submission.field_values[field.id]) }}
              </span>
              <span v-else>
                {{ submission.field_values[field.id] || '-' }}
              </span>
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

      <div class="table-footer">
        <p class="total-count">Total Submissions: {{ submissions.length }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSubmissionsStore } from '@/stores/submissions'
import { useFormsStore } from '@/stores/forms'

const route = useRoute()
const router = useRouter()
const submissionsStore = useSubmissionsStore()
const formsStore = useFormsStore()

const formId = ref(route.params.id)
const currentForm = ref(null)

const submissions = computed(() => submissionsStore.submissions)

const formName = computed(() => {
  return currentForm.value?.name || 'Form'
})

const allFields = computed(() => {
  if (!currentForm.value?.categories) return []
  
  const fields = []
  currentForm.value.categories.forEach(category => {
    if (category.custom_fields) {
      category.custom_fields.forEach(field => {
        if (!fields.find(f => f.id === field.id)) {
          fields.push(field)
        }
      })
    }
  })
  return fields
})

onMounted(async () => {
  await loadFormAndSubmissions()
})

async function loadFormAndSubmissions() {
  try {
    currentForm.value = await formsStore.fetchForm(formId.value)
  
    await submissionsStore.fetchSubmissions(formId.value)
  } catch (error) {
    console.error('Error loading data:', error)
  }
}

async function refreshSubmissions() {
  await loadFormAndSubmissions()
}

async function deleteSubmission(id) {
  if (!confirm('Are you sure you want to delete this submission?')) return
  
  try {
    await submissionsStore.deleteSubmission(id)
  } catch (error) {
    alert('Error deleting submission: ' + error.message)
  }
}

function fillForm() {
  router.push(`/forms/${formId.value}/fill`)
}

function goBack() {
  router.push('/forms')
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

.header-left {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.header-left h2 {
  font-size: 2rem;
  color: #2d3748;
  margin: 0;
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
}

.btn-back {
  background: #e2e8f0;
  color: #4a5568;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  align-self: flex-start;
}

.btn-back:hover {
  background: #cbd5e0;
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

.loading, .error {
  text-align: center;
  padding: 3rem;
  font-size: 1.125rem;
}

.error {
  color: #e53e3e;
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

.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  overflow: hidden;
}

.submissions-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.submissions-table thead {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
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
  opacity: 0.8;
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

.id-column {
  width: 60px;
  font-weight: 600;
  color: #667eea;
}

.field-value {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.date-column {
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
  padding: 1rem;
  background: #f7fafc;
  border-top: 2px solid #e2e8f0;
  text-align: right;
}

.total-count {
  font-weight: 600;
  color: #4a5568;
  margin: 0;
}
</style>