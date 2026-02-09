import { defineStore } from 'pinia'
import api from '@/services/api'

export const useCategoriesStore = defineStore('categories', {
  state: () => ({
    categories: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchCategories() {
      this.loading = true
      this.error = null
      try {
        const response = await api.getCategories()
        this.categories = response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching categories:', error)
      } finally {
        this.loading = false
      }
    },

    async createCategory(data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.createCategory(data)
        this.categories.push(response.data.data)
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error creating category:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateCategory(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.updateCategory(id, data)
        const index = this.categories.findIndex(cat => cat.id === id)
        if (index !== -1) {
          this.categories[index] = response.data.data
        }
        return response.data.data
      } catch (error) {
        this.error = error.message
        console.error('Error updating category:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteCategory(id) {
      this.loading = true
      this.error = null
      try {
        await api.deleteCategory(id)
        this.categories = this.categories.filter(cat => cat.id !== id)
      } catch (error) {
        this.error = error.message
        console.error('Error deleting category:', error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})