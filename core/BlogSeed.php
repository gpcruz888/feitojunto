<?php
/**
 * Conteúdo editorial inicial do blog da feito.
 */
class BlogSeed {
    private const DEMO_SLUGS = [
        'o-poder-do-design-minimalista-na-conversao-de-leads-b2b',
        'performance-web-em-2026-por-que-cada-milissegundo-conta',
        'presenca-digital-de-alto-impacto-alem-do-site-institucional',
    ];

    public static function sync(PDO $db): void {
        $adminId = (int)$db->query("SELECT id FROM admins ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($adminId < 1) {
            return;
        }

        $catId = (int)$db->query("SELECT id FROM categories WHERE slug = 'design-e-produto' LIMIT 1")->fetchColumn();
        if ($catId < 1) {
            $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)")->execute([
                'Design & Produto',
                'design-e-produto',
                'Artigos sobre UI/UX, design de interface e produtos digitais.',
            ]);
            $catId = (int)$db->lastInsertId();
        }

        $del = $db->prepare("DELETE FROM posts WHERE slug = ?");
        foreach (self::DEMO_SLUGS as $slug) {
            $del->execute([$slug]);
        }

        foreach (self::posts() as $post) {
            self::upsertPost($db, $adminId, $catId, $post);
        }
    }

    private static function upsertPost(PDO $db, int $adminId, int $catId, array $post): void {
        $check = $db->prepare("SELECT id FROM posts WHERE slug = ? LIMIT 1");
        $check->execute([$post['slug']]);
        $existingId = (int)$check->fetchColumn();
        $now = date('Y-m-d H:i:s');

        if ($existingId > 0) {
            return;
        } else {
            $ins = $db->prepare("
                INSERT INTO posts (
                    author_id, category_id, title, slug, excerpt, content, cover_image,
                    status, seo_title, seo_description, og_title, og_description, og_image,
                    published_at, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'PUBLICADO', ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $adminId,
                $catId,
                $post['title'],
                $post['slug'],
                $post['excerpt'],
                $post['content'],
                $post['cover_image'],
                $post['seo_title'],
                $post['seo_description'],
                $post['seo_title'],
                $post['seo_description'],
                $post['cover_image'],
                $post['published_at'],
                $now,
                $now,
            ]);
            $postId = (int)$db->lastInsertId();
        }

        self::syncTags($db, $postId, $post['tags']);
    }

    private static function syncTags(PDO $db, int $postId, array $names): void {
        $db->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$postId]);
        $find = $db->prepare("SELECT id FROM tags WHERE slug = ? LIMIT 1");
        $insTag = $db->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $link = $db->prepare("INSERT OR IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)");

        foreach ($names as $name) {
            $slug = Helpers::slugify($name);
            $find->execute([$slug]);
            $tagId = (int)$find->fetchColumn();
            if ($tagId < 1) {
                $insTag->execute([$name, $slug]);
                $tagId = (int)$db->lastInsertId();
            }
            $link->execute([$postId, $tagId]);
        }
    }

    private static function posts(): array {
        return [
            [
                'title' => 'Uma interface bonita não basta: o que realmente faz uma UI funcionar?',
                'slug' => 'uma-interface-bonita-nao-basta',
                'excerpt' => 'Uma boa interface não é só bonita. Ela precisa orientar, facilitar e reduzir esforço — da hierarquia visual à tipografia, cores, botões e ícones.',
                'cover_image' => 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?auto=format&fit=crop&w=1200&q=80',
                'published_at' => '2026-09-06 09:00:00',
                'seo_title' => 'Uma interface bonita não basta: o que faz uma UI funcionar?',
                'seo_description' => 'Entenda os principais fundamentos de uma boa interface, desde hierarquia visual e tipografia até cores, espaçamento, botões e ícones.',
                'tags' => ['UI Design', 'UX Design', 'Design de Interfaces', 'Product Design', 'Usabilidade', 'Design Digital'],
                'reading_time' => 6,
                'content' => <<<'HTML'
<p>Uma interface pode ter uma ótima paleta de cores, uma tipografia bonita e cards perfeitamente alinhados. Ainda assim, pode ser difícil de usar.</p>
<p>Isso acontece porque <strong>design de interface não é apenas sobre aparência</strong>. É sobre criar uma experiência visual que ajude a pessoa a entender o que está acontecendo, identificar o que importa e saber qual ação tomar.</p>
<p>Pequenas decisões de UI têm um impacto enorme nesse processo. O espaçamento entre dois elementos, o tamanho de uma fonte, a escolha de uma cor ou até mesmo a forma de um botão podem facilitar ou dificultar uma tarefa.</p>
<p>O <em>Guia de boas práticas aplicáveis para a criação de interfaces</em>, usado como referência para este conteúdo, reúne dezenas de recomendações justamente sobre esses detalhes, passando por tipografia, cores, botões, grids, ícones, imagens e outros elementos de interface.</p>
<p>E é aí que começa a conversa.</p>

<h2>1. A pessoa precisa saber onde olhar primeiro</h2>
<p>Imagine abrir uma tela e encontrar dez informações com exatamente o mesmo tamanho, peso e destaque.</p>
<p>Por onde você começa?</p>
<p>Uma interface sem hierarquia visual obriga a pessoa a descobrir sozinha o que é importante. O resultado pode ser confusão, esforço desnecessário e uma experiência mais cansativa.</p>
<p>A hierarquia textual existe justamente para criar uma ordem de leitura. Títulos, subtítulos, informações secundárias e textos de apoio precisam ter diferentes níveis de importância visual.</p>
<p>Isso pode ser construído usando:</p>
<ul>
<li>tamanho;</li>
<li>peso da fonte;</li>
<li>cor;</li>
<li>contraste;</li>
<li>espaçamento;</li>
<li>posicionamento.</li>
</ul>
<p>O princípio é simples: <strong>informações diferentes não precisam parecer iguais</strong>.</p>
<p>Quando tudo chama atenção, nada chama atenção.</p>

<h2>2. Espaço vazio também é informação</h2>
<p>Um dos elementos mais subestimados de uma interface é aquilo que não está ali.</p>
<p>O espaço em branco ajuda a separar grupos, criar ritmo e permitir que a interface respire. Quando todos os elementos estão muito próximos, eles começam a parecer parte de um mesmo bloco, mesmo quando possuem funções diferentes.</p>
<p>O mesmo vale para grids.</p>
<p>Manter espaçamentos consistentes entre elementos horizontais e verticais contribui para uma percepção maior de organização e equilíbrio.</p>
<p>Por isso, antes de adicionar mais uma borda, sombra ou divisor, vale fazer uma pergunta:</p>
<p><strong>será que eu realmente preciso de outro elemento ou apenas de mais espaço?</strong></p>
<p>Muitas vezes, alguns pixels resolvem um problema que parecia precisar de um componente inteiro.</p>

<h2>3. Cor não deve competir pela atenção</h2>
<p>A cor primária da marca é importante. Mas isso não significa que ela precisa aparecer em todos os lugares.</p>
<p>Usar a mesma cor repetidamente pode fazer com que ela perca justamente aquilo que deveria oferecer: <strong>destaque</strong>.</p>
<p>Uma abordagem apresentada no material é organizar a paleta por função, separando cores primárias, secundárias, estados de sucesso, alerta e outras necessidades da interface.</p>
<p>Uma referência apresentada no guia é a proporção <strong>60-30-10</strong>:</p>
<ul>
<li><strong>60%</strong> para a cor base</li>
<li><strong>30%</strong> para a cor secundária</li>
<li><strong>10%</strong> para a cor de destaque</li>
</ul>
<p>Não é uma fórmula que precisa ser seguida religiosamente. É uma maneira de pensar sobre distribuição e prioridade.</p>
<p>Se tudo é destaque, o destaque deixa de existir.</p>

<h2>4. Botões precisam deixar claro o que acontece depois</h2>
<p>Um dos erros mais comuns em interfaces é apresentar várias ações com o mesmo peso visual.</p>
<p>Imagine um card com:</p>
<ul>
<li><strong>Comprar</strong></li>
<li><strong>Ver formas de pagamento</strong></li>
<li><strong>Informações do produto</strong></li>
</ul>
<p>Se os três botões possuem exatamente o mesmo destaque, a interface está deixando uma decisão importante para a pessoa usuária: descobrir qual ação é a principal.</p>
<p>A recomendação é criar uma distinção clara entre ações primárias e secundárias. A ação mais importante deve ser identificada com facilidade.</p>
<p>Além disso, um botão não existe apenas no seu estado “normal”.</p>
<p>É preciso pensar também em estados como:</p>
<ul>
<li>hover;</li>
<li>pressionado;</li>
<li>foco;</li>
<li>desabilitado;</li>
<li>carregando.</li>
</ul>
<p>Uma interface consistente não mostra apenas <strong>o que um botão é</strong>. Ela também comunica <strong>o que está acontecendo quando interagimos com ele</strong>.</p>

<h2>5. Ícones não são decoração</h2>
<p>Um ícone ocupa pouco espaço, mas precisa comunicar bastante.</p>
<p>Por isso, quanto menor o espaço disponível, mais importante é que ele seja reconhecível.</p>
<p>O guia recomenda optar por ícones simples, principalmente em interfaces mobile, evitando desenhos excessivamente detalhados que podem perder legibilidade em tamanhos pequenos.</p>
<p>Também existe uma questão de consistência.</p>
<p>Se um conjunto de ícones possui espaçamentos diferentes, pesos diferentes ou estilos completamente distintos, a interface começa a parecer montada por partes.</p>
<p>O espaçamento entre ícones, por exemplo, deve seguir um padrão consistente para preservar ritmo e agrupamento visual.</p>
<p>Não é sobre escolher o ícone mais bonito.</p>
<p>É sobre escolher aquele que <strong>comunica melhor</strong>.</p>

<h2>6. Tipografia é parte da experiência</h2>
<p>Escolher uma fonte é apenas o começo.</p>
<p>Depois dela vêm tamanho, peso, largura da coluna, espaçamento entre linhas e entre caracteres.</p>
<p>Um line-height muito pequeno pode deixar um texto apertado e difícil de escanear. Um espaçamento exagerado pode separar visualmente linhas que deveriam ser percebidas como um único bloco.</p>
<p>Como referência, o material sugere trabalhar geralmente entre <strong>120% e 150%</strong> de espaçamento entre linhas, considerando que o valor ideal varia de acordo com a fonte e seu tamanho.</p>
<p>Também vale evitar transformar um parágrafo inteiro em destaque.</p>
<p>Fontes muito pesadas funcionam melhor para enfatizar informações específicas. Quando tudo está em bold ou ExtraBold, a própria hierarquia perde força.</p>

<h2>7. A interface precisa funcionar antes de impressionar</h2>
<p>Talvez esse seja o ponto mais importante.</p>
<p>É fácil se apaixonar por uma interface porque ela parece sofisticada. Gradientes, sombras, animações, efeitos e componentes elaborados podem deixar um projeto visualmente interessante.</p>
<p>Mas nada disso resolve uma interface que não deixa claro:</p>
<ul>
<li><strong>Onde estou?</strong></li>
<li><strong>O que posso fazer?</strong></li>
<li><strong>O que acabou de acontecer?</strong></li>
<li><strong>O que devo fazer agora?</strong></li>
</ul>
<p>Design de UI está nos grandes elementos, mas também está nos detalhes quase invisíveis.</p>
<p>Está no espaço entre dois componentes.</p>
<p>Na diferença entre um título e um parágrafo.</p>
<p>Na escolha de uma cor.</p>
<p>Na posição de um botão.</p>
<p>Na simplicidade de um ícone.</p>
<p>Na forma como uma tela vazia orienta a próxima ação, em vez de simplesmente informar que “não há nada aqui”. O material, inclusive, trata estados vazios como uma oportunidade de manter a pessoa engajada e sugerir caminhos úteis.</p>

<h2>No fim, uma boa UI não precisa gritar</h2>
<p>Uma boa interface não deveria exigir que a pessoa pare para descobrir como utilizá-la.</p>
<p>Ela deve conduzir.</p>
<p>E essa condução acontece por meio de uma série de pequenas decisões que, isoladamente, parecem quase insignificantes. Juntas, elas constroem a experiência.</p>
<p>É por isso que <strong>UI não é simplesmente deixar uma tela bonita</strong>.</p>
<p>É organizar informação.</p>
<p>Criar prioridade.</p>
<p>Reduzir ruído.</p>
<p>Antecipar dúvidas.</p>
<p>Facilitar decisões.</p>
<p>E, principalmente, fazer tudo isso sem colocar o design entre a pessoa e aquilo que ela veio fazer.</p>
<p><strong>No Feito, é esse tipo de design que queremos discutir por aqui.</strong></p>
<p>Design que não existe apenas para ser visto, mas para <strong>ser usado</strong>.</p>
HTML,
            ],
            [
                'title' => 'Tipografia na interface: quando uma fonte bonita começa a atrapalhar',
                'slug' => 'tipografia-na-ui-como-usar-fontes-em-interfaces',
                'excerpt' => 'Tipografia em uma interface não existe apenas para deixar a tela bonita. Ela precisa ajudar alguém a ler, entender, encontrar e decidir.',
                'cover_image' => 'https://images.unsplash.com/photo-1516962215378-7fa2e137ae93?auto=format&fit=crop&w=1200&q=80',
                'published_at' => '2026-09-04 09:00:00',
                'seo_title' => 'Tipografia na UI: como escolher e usar fontes em interfaces',
                'seo_description' => 'Entenda como tipografia, hierarquia, espaçamento, peso e alinhamento influenciam a experiência de uso em interfaces digitais.',
                'tags' => ['UI Design', 'Tipografia', 'UX Design', 'Design de Interfaces', 'Product Design', 'Usabilidade'],
                'reading_time' => 5,
                'content' => <<<'HTML'
<p>Escolher uma fonte para um projeto costuma parecer uma decisão estética.</p>
<p>Você abre o Figma, testa algumas famílias, compara pesos, encontra uma que combina com a identidade da marca e pronto.</p>
<p>Só que tipografia em uma interface não existe apenas para deixar uma tela bonita.</p>
<p>Ela precisa ajudar alguém a <strong>ler, entender, encontrar e decidir</strong>.</p>
<p>E é justamente aí que algumas escolhas aparentemente pequenas começam a fazer diferença.</p>

<h2>Uma fonte pode ter o estilo certo e ainda estar sendo usada do jeito errado</h2>
<p>Não existe uma fonte universalmente boa ou ruim.</p>
<p>O que existe é uma fonte adequada, ou inadequada, para determinado contexto.</p>
<p>Uma tipografia manuscrita pode funcionar perfeitamente em uma marca de produtos artesanais, por exemplo. Mas provavelmente não seria a melhor escolha para uma interface que comunica segurança, dados financeiros ou informações técnicas.</p>
<p>O material chama atenção justamente para isso: diferentes estilos tipográficos precisam conversar com o contexto e com a sensação que o produto pretende transmitir.</p>
<p>A pergunta, portanto, não deveria ser:</p>
<p><strong>“Essa fonte é bonita?”</strong></p>
<p>Mas sim:</p>
<p><strong>“Essa fonte faz sentido aqui?”</strong></p>

<h2>Hierarquia não é simplesmente deixar o título maior</h2>
<p>Quando pensamos em hierarquia tipográfica, é comum imaginar uma fórmula simples:</p>
<p><strong>Título grande → subtítulo médio → texto pequeno.</strong></p>
<p>Funciona, mas a hierarquia pode ir muito além disso.</p>
<p>Podemos criar diferentes níveis usando:</p>
<ul>
<li>tamanho;</li>
<li>peso;</li>
<li>cor;</li>
<li>contraste;</li>
<li>espaçamento;</li>
<li>posicionamento.</li>
</ul>
<p>O objetivo é estabelecer uma ordem de leitura.</p>
<p>A pessoa deve conseguir identificar rapidamente o que é mais importante e o que é complementar. Quando todos os elementos possuem o mesmo peso visual, as informações começam a disputar atenção.</p>
<p>É quase como uma conversa em grupo.</p>
<p>Se todo mundo fala ao mesmo tempo, ninguém consegue entender nada.</p>

<h2>Cuidado com o texto em negrito</h2>
<p>Existe uma tentação recorrente em UI:</p>
<blockquote><p>Esse texto é importante. Vou colocar em bold.</p></blockquote>
<p>E então mais um trecho vira bold.</p>
<p>E mais um.</p>
<p>Até que chegamos ao maravilhoso momento em que <strong>tudo está em negrito e nada está em destaque</strong>.</p>
<p>Pesos mais fortes funcionam melhor quando utilizados para enfatizar informações específicas. Aplicá-los em blocos inteiros de texto pode prejudicar a fluidez da leitura e fazer com que a hierarquia perca sentido.</p>
<p>O destaque precisa ser escasso para continuar sendo destaque.</p>

<h2>E aquele parágrafo gigantesco?</h2>
<p>Outro problema bastante comum é transformar uma tela em uma parede de texto.</p>
<p>Mesmo que o conteúdo seja excelente, um bloco enorme sem pausas visuais pode desencorajar a leitura.</p>
<p>Dividir o conteúdo em parágrafos menores cria pausas e facilita a escaneabilidade.</p>
<p>Isso também ajuda a pessoa a localizar rapidamente a informação que procura.</p>
<p>Em interfaces, raramente alguém lê tudo de cima para baixo como se estivesse lendo um livro.</p>
<p>A pessoa procura.</p>
<p>Escaneia.</p>
<p>Volta.</p>
<p>Pula.</p>
<p>Compara.</p>
<p>Por isso, o texto precisa colaborar com esse comportamento.</p>

<h2>O espaço entre as linhas também importa</h2>
<p>Duas interfaces podem utilizar exatamente a mesma fonte e o mesmo tamanho de texto e ainda assim proporcionar experiências completamente diferentes.</p>
<p>A diferença pode estar no <strong>line-height</strong>.</p>
<p>Pouco espaçamento deixa as linhas apertadas e dificulta a leitura. Espaçamento demais pode fazer com que linhas que deveriam pertencer ao mesmo bloco pareçam desconectadas.</p>
<p>Como referência, o material trabalha geralmente com algo entre <strong>120% e 150%</strong>, dependendo da família e do tamanho da fonte. Para parágrafos, também apresenta o cálculo de aproximadamente <strong>1,6 × o tamanho da fonte</strong> como ponto de partida.</p>
<p>Não é uma receita universal.</p>
<p>É um ponto de partida para testar.</p>

<h2>Nem tudo precisa estar em CAIXA ALTA</h2>
<p>Textos inteiros em letras maiúsculas podem parecer mais fortes visualmente.</p>
<p>Mas força visual não significa necessariamente melhor leitura.</p>
<p>O material aponta que textos longos em caixa alta exigem mais esforço para serem interpretados e escaneados, justamente porque esse formato é menos comum na leitura cotidiana.</p>
<p>Isso não significa abandonar o recurso.</p>
<p>CAIXA ALTA pode funcionar muito bem em:</p>
<ul>
<li>pré-títulos</li>
<li>call to actions</li>
<li>títulos curtos</li>
</ul>
<p>O problema começa quando ela deixa de ser um recurso de destaque e passa a ser o padrão para todo o conteúdo.</p>

<h2>E talvez o erro mais silencioso: justificar o texto</h2>
<p>No design editorial, o texto justificado pode fazer sentido.</p>
<p>Em interfaces, porém, ele pode criar espaços irregulares entre as palavras, conhecidos como “rios”, interrompendo o ritmo natural da leitura.</p>
<p>Na maioria dos casos, o alinhamento à esquerda oferece um fluxo de leitura mais previsível.</p>
<p>A margem direita irregular não é necessariamente um problema.</p>
<p>Na verdade, pode ser exatamente o que torna a leitura mais confortável.</p>

<h2>Tipografia é parte da usabilidade</h2>
<p>Quando falamos sobre UI, tipografia costuma entrar na conversa como uma questão visual.</p>
<p>Mas ela é também uma questão funcional.</p>
<p>Uma escolha tipográfica pode:</p>
<ul>
<li><strong>facilitar ou dificultar a leitura.</strong></li>
<li><strong>criar ou destruir hierarquia.</strong></li>
<li><strong>orientar ou confundir.</strong></li>
<li><strong>dar personalidade ou transmitir a sensação errada.</strong></li>
</ul>
<p>No fim, escolher uma fonte é apenas o começo.</p>
<p>O verdadeiro trabalho está em descobrir <strong>como ela deve se comportar dentro da interface</strong>.</p>
<p>Porque uma boa tipografia não pede atenção.</p>
<p>Ela organiza a atenção.</p>
HTML,
            ],
            [
                'title' => 'Cor em UI: quando usar mais cores deixa a interface pior',
                'slug' => 'cor-em-ui-como-usar-cores-em-interfaces',
                'excerpt' => 'Uma interface mais colorida não é automaticamente mais interessante. Cor também precisa de hierarquia — para orientar ações, indicar estados e reduzir ruído.',
                'cover_image' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?auto=format&fit=crop&w=1200&q=80',
                'published_at' => '2026-09-02 09:00:00',
                'seo_title' => 'Cor em UI: como usar cores de forma eficiente em interfaces',
                'seo_description' => 'Entenda como usar cores em interfaces para criar hierarquia, orientar ações, melhorar a compreensão e evitar excesso visual.',
                'tags' => ['UI Design', 'Cores', 'UX Design', 'Design de Interfaces', 'Acessibilidade', 'Product Design'],
                'reading_time' => 5,
                'content' => <<<'HTML'
<p>Existe uma armadilha bastante comum no design de interfaces: achar que uma interface mais colorida é automaticamente mais interessante.</p>
<p>Nem sempre.</p>
<p>Às vezes, adicionar uma nova cor parece uma boa solução. Um botão fica laranja, outro azul, um alerta fica vermelho, alguns cards ganham fundos diferentes e, quando percebemos, a interface está competindo consigo mesma.</p>
<p><strong>Cor também precisa de hierarquia.</strong></p>
<p>Uma boa paleta não serve apenas para deixar o produto visualmente agradável. Ela ajuda a pessoa usuária a identificar estados, reconhecer ações e entender o que merece atenção.</p>

<h2>A cor da marca não precisa estar em tudo</h2>
<p>Um dos erros mais comuns é utilizar a cor primária da marca em excesso.</p>
<p>Se a cor principal aparece em botões, títulos, ícones, backgrounds, bordas, cards e praticamente todos os componentes, ela deixa de funcionar como elemento de destaque.</p>
<p>O próprio material recomenda definir uma paleta segmentada por contexto, considerando cores primárias, secundárias, sucesso, alerta e aviso.</p>
<p>A lógica é simples:</p>
<p><strong>se tudo possui a mesma importância visual, nada possui prioridade.</strong></p>
<p>A cor da marca pode continuar sendo protagonista sem precisar aparecer em todas as cenas.</p>

<h2>60-30-10: uma forma simples de pensar a distribuição</h2>
<p>Uma das referências apresentadas no guia é a regra <strong>60-30-10</strong>:</p>
<ul>
<li><strong>60%</strong> — cor base e dominante</li>
<li><strong>30%</strong> — cor secundária</li>
<li><strong>10%</strong> — cor de destaque</li>
</ul>
<p>Não precisa tratar esses números como uma lei gravada em pedra.</p>
<p>A ideia é criar uma distribuição que permita que a cor de destaque realmente tenha força.</p>
<p>Imagine uma tela predominantemente neutra com alguns elementos utilizando a cor principal da marca.</p>
<p>Agora imagine essa mesma tela em que praticamente todos os componentes possuem essa cor.</p>
<p>No primeiro caso, seu olhar encontra um caminho.</p>
<p>No segundo, ele encontra um carnaval corporativo.</p>

<h2>Cor também pode orientar decisões</h2>
<p>Uma boa utilização de cores pode reduzir o tempo necessário para interpretar uma interface.</p>
<p>Um exemplo simples é a seleção de itens.</p>
<p>Imagine uma lista com dez opções. Apenas uma está selecionada.</p>
<p>Se a seleção for indicada apenas por uma pequena alteração de borda, talvez seja necessário procurar com mais atenção.</p>
<p>Quando existe uma diferenciação visual mais evidente, como uma cor de preenchimento, a identificação pode acontecer mais rapidamente.</p>
<p>Isso transforma a cor em uma ferramenta de <strong>informação</strong>, não apenas de decoração.</p>
<p>E essa diferença é importante.</p>

<h2>Vermelho não deveria significar apenas “uma cor bonita”</h2>
<p>Em interfaces, cores também podem carregar significado.</p>
<p>Sucesso, alerta, erro, informação e ações destrutivas precisam possuir uma lógica visual consistente.</p>
<p>Isso ajuda a pessoa a interpretar situações sem precisar ler cada detalhe da tela.</p>
<p>Mas existe uma questão importante: <strong>não depender apenas da cor para transmitir uma informação</strong>.</p>
<p>Acessibilidade precisa fazer parte dessa decisão. O material destaca a necessidade de considerar pessoas com diferentes formas de percepção de cores, incluindo diferentes tipos de daltonismo.</p>
<p>Por isso, um erro não deveria ser comunicado somente com uma cor vermelha.</p>
<p>Ele pode utilizar também <strong>ícone + mensagem + cor</strong>.</p>
<p>Assim, a informação não depende exclusivamente da capacidade da pessoa de diferenciar determinadas tonalidades.</p>

<h2>O contraste pode destruir uma interface bonita</h2>
<p>Uma combinação pode parecer incrível no Figma e se tornar péssima quando alguém precisa realmente ler o conteúdo.</p>
<p>Isso acontece principalmente quando texto e fundo possuem pouco contraste.</p>
<p>E existe outro detalhe interessante: nem sempre o melhor resultado vem de colocar um cinza neutro atrás de uma interface colorida.</p>
<p>O material apresenta exemplos em que a escolha de um background com uma matiz relacionada à cor predominante da interface gera uma composição mais harmoniosa.</p>
<p>A ideia é fazer as cores conversarem.</p>
<p>Em vez de <strong>cor da interface + cinza aleatório</strong>, podemos pensar em <strong>cor da interface + variações coerentes dessa mesma atmosfera cromática</strong>.</p>

<h2>Existe cor até no que parece cinza</h2>
<p>Esse é um daqueles detalhes pequenos que podem mudar bastante o resultado visual.</p>
<p>No sistema HSB, podemos controlar:</p>
<ul>
<li><strong>H</strong> — Hue / Matiz</li>
<li><strong>S</strong> — Saturação</li>
<li><strong>B</strong> — Brilho</li>
</ul>
<p>Isso permite criar tons neutros que carregam uma pequena influência da matiz principal da interface.</p>
<p>Por exemplo, em vez de utilizar simplesmente um cinza neutro, podemos trabalhar com um tom levemente influenciado pela mesma matiz utilizada no restante da interface.</p>
<p>O resultado tende a criar uma relação mais coerente entre fundo, textos e componentes.</p>
<p>É uma diferença sutil.</p>
<p>E justamente por ser sutil, muita gente não percebe conscientemente.</p>
<p>Mas percebe o resultado.</p>

<h2>Menos cores podem significar mais personalidade</h2>
<p>Existe uma ideia curiosa no design: <strong>usar menos pode fazer uma identidade aparecer mais.</strong></p>
<p>Quando uma interface utiliza muitas cores sem uma função clara, a personalidade visual começa a se diluir.</p>
<p>Quando a paleta é mais controlada, cada cor passa a ter uma função.</p>
<p>O material reforça justamente essa ideia ao recomendar trabalhar com limites de cores para criar interfaces menos sobrecarregadas e permitir que as cores realmente reflitam a personalidade do produto.</p>
<p>Não é sobre deixar tudo preto, branco e cinza.</p>
<p>É sobre saber <strong>quando uma cor precisa entrar em cena</strong>.</p>

<h2>A melhor cor é aquela que ajuda alguém a entender</h2>
<p>Cor pode chamar atenção.</p>
<p>Pode criar identidade.</p>
<p>Pode indicar estado.</p>
<p>Pode separar informações.</p>
<p>Pode facilitar uma decisão.</p>
<p>Pode comunicar um problema.</p>
<p>Pode criar atmosfera.</p>
<p>Mas, em uma interface, ela precisa fazer tudo isso sem virar ruído.</p>
<p>Uma boa paleta não é necessariamente aquela que possui as cores mais bonitas.</p>
<p>É aquela em que <strong>cada cor sabe por que está ali</strong>.</p>
<p>E quando isso acontece, a interface começa a fazer uma coisa interessante:</p>
<p>ela deixa de usar cor apenas para ser bonita e começa a usar cor para <strong>se comunicar</strong>.</p>
HTML,
            ],
            [
                'title' => 'Botões em UI: pequenos elementos que podem mudar toda a experiência',
                'slug' => 'boas-praticas-para-botoes-em-ui',
                'excerpt' => 'Botões parecem simples. Só que é através deles que a pessoa realiza grande parte das ações. Um botão mal projetado transforma uma tarefa simples em confusão.',
                'cover_image' => 'https://images.unsplash.com/photo-1522542550221-31fd19575a2d?auto=format&fit=crop&w=1200&q=80',
                'published_at' => '2026-08-28 09:00:00',
                'seo_title' => 'Botões em UI: boas práticas para criar interfaces melhores',
                'seo_description' => 'Aprenda boas práticas para criar botões em interfaces, trabalhando hierarquia, espaçamento, estados, textos, ícones e consistência visual.',
                'tags' => ['UI Design', 'Botões', 'UX Design', 'Design de Interfaces', 'Usabilidade', 'Product Design'],
                'reading_time' => 5,
                'content' => <<<'HTML'
<p>Botões parecem simples. Um retângulo, uma cor, algumas palavras e pronto.</p>
<p>Só que é justamente através deles que a pessoa usuária realiza grande parte das ações dentro de uma interface. Comprar, cadastrar, salvar, avançar, cancelar, enviar.</p>
<p>Por isso, um botão mal projetado pode transformar uma tarefa simples em uma experiência confusa.</p>
<p>O guia dedica um capítulo inteiro aos botões, com 10 dicas práticas para melhorar sua construção e seu uso nas interfaces.</p>

<h2>Nem todo botão precisa gritar</h2>
<p>Um dos primeiros cuidados é criar uma <strong>hierarquia entre as ações</strong>.</p>
<p>Quando todas as opções possuem o mesmo peso visual, a interface não ajuda a pessoa a entender o que deve fazer primeiro.</p>
<p>A recomendação é diferenciar botões <strong>primários e secundários</strong>. O botão primário representa a ação mais importante daquele contexto, enquanto o secundário fica reservado para ações menos relevantes.</p>
<p>Essa hierarquia pode ser reforçada pela cor, tamanho e posição. O guia recomenda, por exemplo, utilizar a cor principal da marca nos botões primários, mantendo essa cor mais exclusiva para esse tipo de ação.</p>

<h2>O botão também precisa de espaço</h2>
<p>Um botão apertado transmite exatamente isso: aperto.</p>
<p>O <strong>white space</strong> dentro do componente não serve apenas para deixá-lo mais bonito. Ele também aumenta a área disponível para clique ou toque, facilitando a interação.</p>
<p>O material utiliza múltiplos de 8 como referência e cita 36 px como uma altura mínima utilizada para botões de baixa prioridade e 48 px para botões primários em aplicações mobile.</p>
<p>Não é uma fórmula universal. É uma referência para começar.</p>

<h2>Pense no botão antes, durante e depois do clique</h2>
<p>Um erro comum é desenhar apenas o estado “bonito” do botão.</p>
<p>Mas o componente precisa responder à interação.</p>
<p>O guia recomenda prever estados como:</p>
<ul>
<li>padrão</li>
<li>desabilitado</li>
<li>hover</li>
<li>pressionado</li>
<li>foco</li>
<li>carregamento</li>
<li>concluído</li>
</ul>
<p>O estado de foco merece atenção especial por sua relação com acessibilidade.</p>
<p>Uma interface não termina quando o usuário clica. É aí que ela começa a responder.</p>

<h2>Texto de botão não é espaço para explicar tudo</h2>
<p>“Ver portfólio completo” funciona.</p>
<p>Um botão com três linhas explicando o que acontecerá depois do clique, nem tanto.</p>
<p>O material recomenda manter o texto dos botões em <strong>uma única linha</strong> e sugere não ultrapassar três palavras. A ideia é que o botão seja uma chamada para ação direta e concisa, não um pequeno parágrafo disfarçado.</p>
<p>Quando o texto quebra, além do problema visual, o componente pode deixar de ser imediatamente reconhecido como botão.</p>

<h2>Consistência é parte da usabilidade</h2>
<p>Se “Comprar” é um botão azul arredondado em uma tela e aparece como um botão cinza quadrado em outra, alguma coisa está errada.</p>
<p>Não necessariamente porque uma das versões é feia, mas porque a interface deixou de estabelecer uma regra visual consistente.</p>
<p>O guia recomenda definir um padrão de estilo e mantê-lo ao longo das telas. Diferenças em bordas, cores, tamanhos e capitalização podem criar essa inconsistência.</p>

<h2>Ícones podem ajudar, mas precisam fazer sentido</h2>
<p>Um ícone pode reduzir o esforço necessário para reconhecer uma ação. Mas ele não deve existir apenas porque “fica bonito”.</p>
<p>O material recomenda utilizar ícones conhecidos e coerentes com a ação representada. Quando o símbolo não corresponde ao texto ou ao contexto, ele pode gerar justamente o efeito contrário: confusão.</p>
<p>O melhor ícone não é necessariamente o mais criativo. É aquele que a pessoa entende sem precisar decifrá-lo.</p>

<h2>E até a sombra tem uma função</h2>
<p>Outro detalhe abordado no capítulo é o uso de <strong>drop-shadow</strong>.</p>
<p>Sombras podem ajudar a criar profundidade e destacar elementos importantes, mas os valores automáticos dos softwares de design nem sempre produzem um resultado refinado.</p>
<p>O guia ainda sugere, como complemento, utilizar a própria cor de fundo do botão como referência para a sombra, criando um resultado mais suave.</p>

<h2>Formas conhecidas continuam funcionando</h2>
<p>Existe uma tentação de transformar tudo em algo extremamente autoral.</p>
<p>Mas, quando falamos de componentes de interação, familiaridade importa.</p>
<p>O material recomenda preservar formas que já são reconhecidas como botões, como elementos retangulares ou com cantos arredondados. Formas excessivamente orgânicas podem fazer a pessoa questionar se aquilo realmente é clicável.</p>
<p>Criatividade é ótima. Só não precisa esconder a porta de entrada.</p>

<h2>No fim, um botão precisa responder a uma pergunta simples</h2>
<p><strong>“O que acontece quando eu clicar aqui?”</strong></p>
<p>Se a resposta é evidente, o botão está cumprindo seu papel.</p>
<p>Se a pessoa precisa parar para descobrir qual botão é o principal, interpretar um ícone estranho, entender um texto enorme ou descobrir se aquele elemento é realmente clicável, o problema provavelmente não está na pessoa. Está no design.</p>
<p>Botões são pequenos, mas carregam uma responsabilidade enorme: <strong>transformar intenção em ação.</strong></p>
HTML,
            ],
        ];
    }
}
