import AdminRoles from './AdminRoles'
import AdminUsers from './AdminUsers'
import Categories from './Categories'
import ClientVersions from './ClientVersions'
import Contents from './Contents'
import CustomerRequirements from './CustomerRequirements'
import LicensePackages from './LicensePackages'
import Media from './Media'
import OperationLogs from './OperationLogs'
import SystemSettings from './SystemSettings'
import Tags from './Tags'
import Users from './Users'

const Resources = {
    AdminRoles: Object.assign(AdminRoles, AdminRoles),
    AdminUsers: Object.assign(AdminUsers, AdminUsers),
    Categories: Object.assign(Categories, Categories),
    ClientVersions: Object.assign(ClientVersions, ClientVersions),
    Contents: Object.assign(Contents, Contents),
    CustomerRequirements: Object.assign(CustomerRequirements, CustomerRequirements),
    LicensePackages: Object.assign(LicensePackages, LicensePackages),
    Media: Object.assign(Media, Media),
    OperationLogs: Object.assign(OperationLogs, OperationLogs),
    SystemSettings: Object.assign(SystemSettings, SystemSettings),
    Tags: Object.assign(Tags, Tags),
    Users: Object.assign(Users, Users),
}

export default Resources