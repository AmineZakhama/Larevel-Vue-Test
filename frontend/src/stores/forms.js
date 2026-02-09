import { defineStore } from 'pinia'
import api from '@/services/api'

export const useFormsStore = defineStore('forms', {
  state: () => ({
    forms: [],
    currentForm: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchForms() {
      this.loading = true
      this.error = null
      try {
        const response = await api.getForms()
        this.forms = response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching forms:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchForm(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.getForm(id)
        this.currentForm = response.data.data
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching form:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createForm(data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.createForm(data)
        this.forms.push(response.data.data)
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error creating form:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateForm(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.updateForm(id, data)
        const index = this.forms.findIndex(form => form.id === id)
        if (index !== -1) {
          this.forms[index] = response.data.data
        }
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error updating form:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteForm(id) {
      this.loading = true
      this.error = null
      try {
        await api.deleteForm(id)
        this.forms = this.forms.filter(form => form.id !== id)
      } catch (error) {
        this.error = error.message
        console.error('Error deleting form:', error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})