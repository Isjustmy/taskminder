// taskStore.js
import { defineStore } from 'pinia'

export const useTaskStore = defineStore('task', {
  state: () => ({
    taskTitle: ''
  }),
  actions: {
    setTaskTitle(title) {
      this.taskTitle = title
    }
  }
})
