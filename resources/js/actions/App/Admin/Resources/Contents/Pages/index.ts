import ListContents from './ListContents'
import CreateContent from './CreateContent'
import EditContent from './EditContent'

const Pages = {
    ListContents: Object.assign(ListContents, ListContents),
    CreateContent: Object.assign(CreateContent, CreateContent),
    EditContent: Object.assign(EditContent, EditContent),
}

export default Pages