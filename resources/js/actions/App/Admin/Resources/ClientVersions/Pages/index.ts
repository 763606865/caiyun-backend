import ListClientVersions from './ListClientVersions'
import CreateClientVersion from './CreateClientVersion'
import EditClientVersion from './EditClientVersion'

const Pages = {
    ListClientVersions: Object.assign(ListClientVersions, ListClientVersions),
    CreateClientVersion: Object.assign(CreateClientVersion, CreateClientVersion),
    EditClientVersion: Object.assign(EditClientVersion, EditClientVersion),
}

export default Pages