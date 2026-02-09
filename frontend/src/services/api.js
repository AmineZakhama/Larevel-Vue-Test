import axios from 'axios'

const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

export default {
  // Custom Fields
  getCustomFields() {
    return apiClient.get('/custom-fields')
  },
  getAvailableCustomFields(excludeCategoryId = null) {
    const params = excludeCategoryId ? { exclude_category_id: excludeCategoryId } : {}
    return apiClient.get('/custom-fields/available', { params })
  },
  createCustomField(data) {
    return apiClient.post('/custom-fields', data)
  },
  updateCustomField(id, data) {
    return apiClient.put(`/custom-fields/${id}`, data)
  },
  deleteCustomField(id) {
    return apiClient.delete(`/custom-fields/${id}`)
  },

  // Categories
  getCategories() {
    return apiClient.get('/categories')
  },
  getAvailableCategories(excludeFormId = null) {
    const params = excludeFormId ? { exclude_form_id: excludeFormId } : {}
    return apiClient.get('/categories/available', { params })
  },
  createCategory(data) {
    return apiClient.post('/categories', data)
  },
  updateCategory(id, data) {
    return apiClient.put(`/categories/${id}`, data)
  },
  deleteCategory(id) {
    return apiClient.delete(`/categories/${id}`)
  },

  // Forms
  getForms() {
    return apiClient.get('/forms')
  },
  getForm(id) {
    return apiClient.get(`/forms/${id}`)
  },
  createForm(data) {
    return apiClient.post('/forms', data)
  },
  updateForm(id, data) {
    return apiClient.put(`/forms/${id}`, data)
  },
  deleteForm(id) {
    return apiClient.delete(`/forms/${id}`)
  },

  // Form Submissions
  getFormSubmissions(formId) {
    return apiClient.get(`/forms/${formId}/submissions`)
  },
  createSubmission(formId, data) {
    return apiClient.post(`/forms/${formId}/submissions`, data)
  },
  getSubmission(id) {
    return apiClient.get(`/submissions/${id}`)
  },
  deleteSubmission(id) {
    return apiClient.delete(`/submissions/${id}`)
  }
}