import ListMedia from './ListMedia'
import CreateMedia from './CreateMedia'
import EditMedia from './EditMedia'

const Pages = {
    ListMedia: Object.assign(ListMedia, ListMedia),
    CreateMedia: Object.assign(CreateMedia, CreateMedia),
    EditMedia: Object.assign(EditMedia, EditMedia),
}

export default Pages