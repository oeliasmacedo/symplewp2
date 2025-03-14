const app = Vue.createApp({
  data() {
    return {
      currentView: 'dashboard',
      stats: {
        posts: 0,
        pages: 0,
        plugins: 0
      },
      posts: [],
      pages: [],
      plugins: [],
      showModal: false,
      modalType: 'post',
      modalAction: 'create',
      modalData: {
        id: null,
        title: '',
        content: '',
        status: 'publish'
      }
    }
  },
  methods: {
    async loadStats() {
      try {
        const response = await axios.get('/api/stats')
        this.stats = response.data
      } catch (error) {
        console.error('Erro ao carregar estatísticas:', error)
      }
    },
    async loadPosts() {
      try {
        const response = await axios.get('/api/posts')
        this.posts = response.data
      } catch (error) {
        console.error('Erro ao carregar posts:', error)
      }
    },
    async loadPages() {
      try {
        const response = await axios.get('/api/pages')
        this.pages = response.data
      } catch (error) {
        console.error('Erro ao carregar páginas:', error)
      }
    },
    async loadPlugins() {
      try {
        const response = await axios.get('/api/plugins')
        this.plugins = response.data
      } catch (error) {
        console.error('Erro ao carregar plugins:', error)
      }
    },
    openModal(type, action, data = null) {
      this.modalType = type
      this.modalAction = action
      this.modalData = data || {
        id: null,
        title: '',
        content: '',
        status: 'publish'
      }
      this.showModal = true
    },
    async saveModal() {
      try {
        const endpoint = `/api/${this.modalType}s`
        let response
        
        if (this.modalAction === 'create') {
          response = await axios.post(endpoint, this.modalData)
        } else {
          response = await axios.put(`${endpoint}/${this.modalData.id}`, this.modalData)
        }

        this.showModal = false
        this.loadStats()
        
        if (this.modalType === 'post') {
          this.loadPosts()
        } else if (this.modalType === 'page') {
          this.loadPages()
        } else if (this.modalType === 'plugin') {
          this.loadPlugins()
        }
      } catch (error) {
        console.error('Erro ao salvar:', error)
      }
    },
    async deleteItem(type, id) {
      if (!confirm('Tem certeza que deseja excluir este item?')) {
        return
      }

      try {
        await axios.delete(`/api/${type}s/${id}`)
        this.loadStats()
        
        if (type === 'post') {
          this.loadPosts()
        } else if (type === 'page') {
          this.loadPages()
        } else if (type === 'plugin') {
          this.loadPlugins()
        }
      } catch (error) {
        console.error('Erro ao excluir:', error)
      }
    }
  },
  mounted() {
    this.loadStats()
    this.loadPosts()
    this.loadPages()
    this.loadPlugins()
  }
})

app.mount('#app') 