package games

// Game содержит информацию об игре
type Game struct {
	ID           int64   `json:"-"`            // ID игры
	CID          string  `json:"cid"`          // CID игры (compoment ID внутренний идентификатор игры система/группа/идентификатор)
	System       string  `json:"p"`            // Система (провайдер) игры
	Group        string  `json:"g"`            // Группа игры
	GameID       string  `json:"id"`           // ID игры (идентификатор игры в системе игрового провайдера)
	Name         string  `json:"nm"`           // Название игры
	Img          string  `json:"img"`          // Изображение игры
	Category     string  `json:"ct"`           // Категория игры
	LandingGroup string  `json:"-"`            // Группа для отображения на лендинге
	Title        string  `json:"-"`            // Заголовок игры (при установке заменяет имя игры)
	Device       string  `json:"dv"`           // Устройство, на котором игра доступна (например, "desktop", "mobile", any)
	Tags         int     `json:"tgs"`          // Теги игры (например, "hot", "new", "top", "jackpot", "fs", "megaways", "crash")
	LndFlags     int     `json:"lf"`           // Флаги для лендинга (например, "topSlotGames", "topTableGames", "topLiveGames")
	IsTest       *bool   `json:"t,omitempty"`  // Флаг тестовой игры (игра доступна только для тестирования (тестовыми пользователями) и не отображается в общем списке)
	CustomImg    *string `json:"ci,omitempty"` // Пользовательское изображение игры (если указано, то используется вместо стандартного изображения)
	Priority     int64   `json:"-"`            // Приоритет игры (используется для сортировки игр в списке)
	LandPriority int64   `json:"-"`            // Приоритет игры на лендинге (используется для сортировки игр в списке)
}

// SetTag устанавливает тег для игры
// tag - тег игры (например, "hot", "new", "top", "jackpot", "fs", "megaways", "crash")
func (g *Game) SetTag(tag string) {
	switch tag {
	case "hot":
		g.Tags = g.Tags | TAG_HOT
	case "new":
		g.Tags = g.Tags | TAG_NEW
	case "top":
		g.Tags = g.Tags | TAG_TOP
	case "jackpot":
		g.Tags = g.Tags | TAG_JACKPOT
	case "fs":
		g.Tags = g.Tags | TAG_FS
	case "megaways":
		g.Tags = g.Tags | TAG_MW
	case "crash":
		g.Tags = g.Tags | TAG_CRASH
	}
}

// HasTag проверяет, установлен ли тег для игры
// tag - тег игры (например, "hot", "new", "top", "jackpot", "fs", "megaways", "crash")
func (g *Game) HasTag(tag int) bool {
	return (g.Tags & tag) != 0
}

// SetLndFlag устанавливает флаг для лендинга
func (g *Game) SetLndFlag(cat string) {
	switch cat {
	case "topSlotGames":
		g.LndFlags = g.LndFlags | LND_FLAG_SLOT
	case "topTableGames":
		g.LndFlags = g.LndFlags | LND_FLAG_TABLE
	case "topLiveGames":
		g.LndFlags = g.LndFlags | LND_FLAG_LIVE
	}
}

// Доступные теги для игр
const (
	TAG_NEW = 1 << iota
	TAG_HOT
	TAG_JACKPOT
	TAG_FS
	TAG_MW
	TAG_TOP
	TAG_CRASH
)

// CreateTagFlags создает флаги тегов для игры
// string_tags - список тегов (например, "hot", "new", "top", "jackpot", "fs", "megaways", "crash")
func CreateTagFlags(string_tags []string) int {
	tag := 0
	for _, stag := range string_tags {
		switch stag {
		case "new":
			tag = tag | TAG_NEW
		case "hot":
			tag = tag | TAG_HOT
		case "top":
			tag = tag | TAG_TOP
		case "jackpot":
			tag = tag | TAG_JACKPOT
		case "fs":
			tag = tag | TAG_FS
		case "megaways":
			tag = tag | TAG_MW
		case "crash":
			tag = tag | TAG_CRASH
		}
	}
	return tag
}

// Доступные флаги для лендинга
const (
	LND_FLAG_SLOT = 1 << iota
	LND_FLAG_TABLE
	LND_FLAG_LIVE
)
