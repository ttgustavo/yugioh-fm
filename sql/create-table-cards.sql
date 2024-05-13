create table if not exists cards (
    id integer primary key not null,
    name text not null,
    description text not null,
    guardian_star_a integer not null,
    guardian_star_b integer not null,
    level integer not null,
    type integer not null,
    attack integer not null,
    defense integer not null,
    stars integer not null,
    code text not null,
    attribute integer not null
)