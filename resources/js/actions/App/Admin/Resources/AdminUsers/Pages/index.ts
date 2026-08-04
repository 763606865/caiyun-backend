import ListAdminUsers from './ListAdminUsers'
import CreateAdminUser from './CreateAdminUser'
import EditAdminUser from './EditAdminUser'

const Pages = {
    ListAdminUsers: Object.assign(ListAdminUsers, ListAdminUsers),
    CreateAdminUser: Object.assign(CreateAdminUser, CreateAdminUser),
    EditAdminUser: Object.assign(EditAdminUser, EditAdminUser),
}

export default Pages