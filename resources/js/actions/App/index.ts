import Admin from './Admin'
import Api from './Api'

const App = {
    Admin: Object.assign(Admin, Admin),
    Api: Object.assign(Api, Api),
}

export default App