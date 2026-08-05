import Admin from './Admin'
import Api from './Api'
import Http from './Http'

const App = {
    Admin: Object.assign(Admin, Admin),
    Api: Object.assign(Api, Api),
    Http: Object.assign(Http, Http),
}

export default App