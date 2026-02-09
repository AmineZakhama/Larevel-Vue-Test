import { defineStore } from 'pinia'
import api from '@/services/api'

export const useCustomFieldsStore = defineStore('customFields', {
  state: () => ({
    customFields: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchCustomFields() {
      this.loading = true
      this.error = null
      try {
        const response = await api.getCustomFields()
        this.customFields = response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching custom fields:', error)
      } finally {
        this.loading = false
      }
    },

    async createCustomField(data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.createCustomField(data)
        this.customFields.push(response.data.data)
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error creating custom field:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateCustomField(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.updateCustomField(id, data)
        const index = this.customFields.findIndex(field => field.id === id)
        if (index !== -1) {
          this.customFields[index] = response.data.data
        }
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error updating custom field:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteCustomField(id) {
      this.loading = true
      this.error = null
      try {
        await api.deleteCustomField(id)
        this.customFields = this.customFields.filter(field => field.id !== id)
      } catch (error) {
        this.error = error.message
        console.error('Error deleting custom field:', error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})