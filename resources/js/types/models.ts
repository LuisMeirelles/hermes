export type PalavraChaveGherkin = 'dado' | 'quando' | 'entao' | 'e' | 'mas';

export type CenarioStatus =
    'a_fazer' | 'em_andamento' | 'passou' | 'falhou' | 'bloqueado';

export type Severidade = 'bloqueante' | 'critica' | 'maior' | 'menor';

export type TesteStatus =
    'nao_iniciado' | 'em_andamento' | 'passou' | 'falhou' | 'parcial';

export type CasoDeTestePasso = {
    id?: number;
    ordem: number;
    palavra_chave: PalavraChaveGherkin;
    texto: string;
};

export type CasoDeTeste = {
    id: number;
    titulo: string;
    descricao: string | null;
    passos: CasoDeTestePasso[];
    passos_count?: number;
    created_at: string;
    updated_at: string;
};

export type Cenario = {
    id: number;
    teste_id: number;
    caso_de_teste_id: number | null;
    cloned_from_cenario_id: number | null;
    titulo: string;
    passos_snapshot: CasoDeTestePasso[];
    status: CenarioStatus;
    severidade: Severidade;
    created_at: string;
    updated_at: string;
};

export type Teste = {
    id: number;
    repo_name: string;
    issue_number: number;
    titulo: string | null;
    status: TesteStatus;
    percent_complete: string;
    created_at: string;
    updated_at: string;
};

export type GithubRepositorio = {
    name: string;
    full_name: string;
};

export type GithubIssuePreview = {
    title: string;
    state: string;
    html_url: string;
};
