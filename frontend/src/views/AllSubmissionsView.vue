<template>
  <div class="submissions-page">
    <div class="page-header">
      <h2>All Submissions</h2>
      <div class="header-actions">
        <!-- Export Dropdown -->
        <div class="export-dropdown">
          <button
            @click="showExportMenu = !showExportMenu"
            class="btn btn-primary"
          >
            📥 Export
          </button>
          <div v-if="showExportMenu" class="dropdown-menu">
            <button @click="exportSubmissions('excel')" class="dropdown-item">
              📊 Export to Excel
            </button>
            <button @click="exportSubmissions('csv')" class="dropdown-item">
              📄 Export to CSV
            </button>
            <button @click="exportSubmissions('pdf')" class="dropdown-item">
              📑 Export to PDF
            </button>
          </div>
        </div>
        <button @click="refreshData" class="btn btn-secondary">
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
      <div class="filter-row">
        <div class="filter-item">
          <label>Filter by Form:</label>
          <select
            v-model="filterFormId"
            @change="applyFilters"
            class="filter-select"
          >
            <option value="">All Forms</option>
            <option
              v-for="form in formsStore.forms"
              :key="form.id"
              :value="form.id"
            >
              {{ form.name }}
            </option>
          </select>
        </div>
        <div class="filter-item">
          <label>From Date:</label>
          <input
            v-model="filterDateFrom"
            @change="applyFilters"
            type="date"
            class="filter-input"
          />
        </div>
        <div class="filter-item">
          <label>To Date:</label>
          <input
            v-model="filterDateTo"
            @change="applyFilters"
            type="date"
            class="filter-input"
          />
        </div>
        <button @click="clearFilters" class="btn btn-secondary-small">
          Clear Filters
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading">Loading submissions...</div>

    <div
      v-if="!loading && filteredSubmissions.length === 0"
      class="empty-state"
    >
      <p>No submissions found.</p>
      <router-link to="/forms" class="btn btn-primary"
        >Create a Form</router-link
      >
    </div>

    <div
      v-if="!loading && filteredSubmissions.length > 0"
      class="table-wrapper"
    >
      <div class="table-container">
        <table class="submissions-table">
          <thead>
            <tr>
              <th class="drag-column">⋮⋮</th>
              <th class="form-name-column">Form Name</th>
              <th
                v-for="field in allUniqueFields"
                :key="field.id"
                class="field-column"
              >
                {{ field.name }}
                <span class="field-type-hint">{{ field.field_type }}</span>
              </th>
              <th class="date-column">Submitted At</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>
          <draggable
            v-model="filteredSubmissions"
            tag="tbody"
            item-key="id"
            @end="onDragEnd"
            handle=".drag-handle"
          >
            <template #item="{ element: submission }">
              <tr>
                <td class="drag-column">
                  <span class="drag-handle">⋮⋮</span>
                </td>
                <td class="form-name-column">
                  <strong>{{ submission.formName }}</strong>
                </td>
                <td
                  v-for="field in allUniqueFields"
                  :key="field.id"
                  class="field-value"
                >
                  <span v-if="getFieldValue(submission, field.id) !== null">
                    <span v-if="field.field_type === 'checkbox'">
                      {{
                        getFieldValue(submission, field.id) ? "✓ Yes" : "✗ No"
                      }}
                    </span>
                    <span v-else-if="field.field_type === 'date'">
                      {{ formatDate(getFieldValue(submission, field.id)) }}
                    </span>
                    <span v-else>
                      {{ getFieldValue(submission, field.id) }}
                    </span>
                  </span>
                  <span v-else class="empty-cell">-</span>
                </td>
                <td class="date-column">
                  {{ formatDateTime(submission.created_at) }}
                </td>
                <td class="actions-column">
                  <button
                    @click="exportSinglePdf(submission.id)"
                    class="btn btn-export-small"
                    title="Export as PDF"
                  >
                    📄 PDF
                  </button>
                  <button
                    @click="deleteSubmission(submission.id)"
                    class="btn btn-delete-small"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </template>
          </draggable>
        </table>
      </div>

      <div class="table-footer">
        <p class="summary-text">
          <strong>{{ filteredSubmissions.length }}</strong> submission(s)
          displayed
          <span v-if="filterFormId || filterDateFrom || filterDateTo">
            (filtered from {{ allSubmissions.length }} total)
          </span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useFormsStore } from "@/stores/forms";
import { useSubmissionsStore } from "@/stores/submissions";
import { useCustomFieldsStore } from "@/stores/customFields";
import draggable from "vuedraggable";
import api from "@/services/api";

const formsStore = useFormsStore();
const submissionsStore = useSubmissionsStore();
const customFieldsStore = useCustomFieldsStore();

const loading = ref(false);
const allSubmissions = ref([]);
const formsMap = ref(new Map());
const showExportMenu = ref(false);


const filterFormId = ref("");
const filterDateFrom = ref("");
const filterDateTo = ref("");


const filteredSubmissions = computed({
  get() {
    let filtered = [...allSubmissions.value];

    if (filterFormId.value) {
      filtered = filtered.filter((s) => s.form_id == filterFormId.value);
    }

    if (filterDateFrom.value) {
      const fromDate = new Date(filterDateFrom.value);
      filtered = filtered.filter((s) => new Date(s.created_at) >= fromDate);
    }

    if (filterDateTo.value) {
      const toDate = new Date(filterDateTo.value);
      toDate.setHours(23, 59, 59, 999); // End of day
      filtered = filtered.filter((s) => new Date(s.created_at) <= toDate);
    }

    return filtered;
  },
  set(value) {
    // This setter is for vuedraggable
    allSubmissions.value = value;
  },
});


const allUniqueFields = computed(() => {
  const fieldsMap = new Map();

  formsStore.forms.forEach((form) => {
    if (form.categories) {
      form.categories.forEach((category) => {
        if (category.custom_fields) {
          category.custom_fields.forEach((field) => {
            if (!fieldsMap.has(field.id)) {
              fieldsMap.set(field.id, field);
            }
          });
        }
      });
    }
  });

  return Array.from(fieldsMap.values());
});

onMounted(async () => {
  await loadAllData();
});

async function loadAllData() {
  loading.value = true;
  try {
    await formsStore.fetchForms();
    await customFieldsStore.fetchCustomFields();

    formsStore.forms.forEach((form) => {
      formsMap.value.set(form.id, form.name);
    });

    const submissionPromises = formsStore.forms.map(async (form) => {
      try {
        await submissionsStore.fetchSubmissions(form.id);
        return submissionsStore.submissions.map((sub) => ({
          ...sub,
          formName: form.name,
        }));
      } catch (error) {
        console.error(`Error loading submissions for form ${form.id}:`, error);
        return [];
      }
    });

    const allSubmissionsArrays = await Promise.all(submissionPromises);
    const flatSubmissions = allSubmissionsArrays
      .flat()
      .sort((a, b) => (a.display_order || 0) - (b.display_order || 0));

    allSubmissions.value = flatSubmissions;
  } catch (error) {
    console.error("Error loading data:", error);
  } finally {
    loading.value = false;
  }
}

async function refreshData() {
  await loadAllData();
}

function getFieldValue(submission, fieldId) {
  if (!submission.field_values) return null;
  return submission.field_values[fieldId] !== undefined
    ? submission.field_values[fieldId]
    : null;
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

async function deleteSubmission(id) {
  if (!confirm("Are you sure you want to delete this submission?")) return;

  try {
    await api.deleteSubmission(id);
    allSubmissions.value = allSubmissions.value.filter((s) => s.id !== id);
  } catch (error) {
    console.error("Error deleting submission:", error);
    alert("Failed to delete submission");
  }
}

function applyFilters() {
  // Filters are automatically applied via computed property
}

function clearFilters() {
  filterFormId.value = "";
  filterDateFrom.value = "";
  filterDateTo.value = "";
}

async function onDragEnd() {
  // Update display_order for all submissions
  const updates = filteredSubmissions.value.map((submission, index) => ({
    id: submission.id,
    order: index,
  }));

  try {
    await api.reorderSubmissions(updates);

  } catch (error) {
    console.error("Error reordering submissions:", error);
    alert("Failed to save new order");
    await loadAllData(); // Reload to revert
  }
}

async function exportSubmissions(format) {
  showExportMenu.value = false;

  try {
    const params = new URLSearchParams({ format });
    if (filterFormId.value) {
      params.append("form_id", filterFormId.value);
    }

    const response = await api.exportSubmissions(params.toString());

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;

    const extension = format === "excel" ? "xlsx" : format;
    link.setAttribute(
      "download",
      `submissions_${new Date().toISOString().split("T")[0]}.${extension}`,
    );

    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Error exporting submissions:", error);
    alert("Failed to export submissions");
  }
}

async function exportSinglePdf(id) {
  try {
    const response = await api.exportSubmissionPdf(id);

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `submission_${id}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Error exporting PDF:", error);
    alert("Failed to export PDF");
  }
}
</script>

<style scoped>
.submissions-page {
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.export-dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 5px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  min-width: 180px;
  z-index: 1000;
}

.dropdown-item {
  width: 100%;
  padding: 10px 15px;
  text-align: left;
  border: none;
  background: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.dropdown-item:hover {
  background-color: #f3f4f6;
}

.dropdown-item:first-child {
  border-radius: 8px 8px 0 0;
}

.dropdown-item:last-child {
  border-radius: 0 0 8px 8px;
}

.filter-panel {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.filter-row {
  display: flex;
  gap: 15px;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.filter-item label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.filter-select,
.filter-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  min-width: 180px;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-secondary-small {
  padding: 8px 16px;
  background-color: #6b7280;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.btn-secondary-small:hover {
  background-color: #4b5563;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.table-wrapper {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.table-container {
  overflow-x: auto;
}

.submissions-table {
  width: 100%;
  border-collapse: collapse;
}

.submissions-table thead {
  background-color: #f3f4f6;
}

.submissions-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
}

.submissions-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
}

.submissions-table tbody tr:hover {
  background-color: #f9fafb;
}

.drag-column {
  width: 40px;
  text-align: center;
}

.drag-handle {
  cursor: grab;
  color: #9ca3af;
  font-size: 18px;
  user-select: none;
}

.drag-handle:active {
  cursor: grabbing;
}

.form-name-column {
  min-width: 200px;
}

.field-column {
  min-width: 150px;
}

.field-type-hint {
  display: block;
  font-size: 11px;
  color: #9ca3af;
  font-weight: normal;
}

.field-value {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
}

.empty-cell {
  color: #9ca3af;
}

.date-column {
  min-width: 150px;
}

.actions-column {
  min-width: 180px;
  text-align: right;
}

.btn-export-small {
  padding: 6px 12px;
  background-color: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  margin-right: 5px;
}

.btn-export-small:hover {
  background-color: #059669;
}

.btn-delete-small {
  padding: 6px 12px;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
}

.btn-delete-small:hover {
  background-color: #dc2626;
}

.table-footer {
  padding: 16px;
  background-color: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.summary-text {
  margin: 0;
  color: #6b7280;
  font-size: 14px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background-color: #4b5563;
}
</style>
