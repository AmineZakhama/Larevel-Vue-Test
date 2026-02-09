import { defineStore } from 'pinia'
import api from '@/services/api'

export const useSubmissionsStore = defineStore('submissions', {
  state: () => ({
    submissions: [],
    currentSubmission: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchSubmissions(formId) {
      this.loading = true
      this.error = null
      try {
        const response = await api.getFormSubmissions(formId)
        this.submissions = response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching submissions:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchSubmission(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.getSubmission(id)
        this.currentSubmission = response.data.data
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching submission:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createSubmission(formId, data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.createSubmission(formId, data)
        this.submissions.push(response.data.data)
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error creating submission:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteSubmission(id) {
      this.loading = true
      this.error = null
      try {
        await api.deleteSubmission(id)
        this.submissions = this.submissions.filter(sub => sub.id !== id)
      } catch (error) {
        this.error = error.message
        console.error('Error deleting submission:', error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})